<?php

namespace Chm\BankFollowUpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ob\HighchartsBundle\Highcharts\Highchart;
use \DateTime;
use \MongoDate;

class GraphController extends Controller
{
	private function generateSerie ( $mongoResult ) {
		$start = 2800;
		$previousValue = $total = $start;
		$graphSerie = [];
		foreach($mongoResult as $element) {
			$total += $element['total'];
			$date = explode('/', $element['_id']);
			$day = (int)$date[0];
			$graphSerie[$day] = $total;
		}
		for($i=0;$i<=31;$i++) {
			if( !array_key_exists($i, $graphSerie)) {
				$graphSerie[$i] = $previousValue;
			} else {
				$previousValue = $graphSerie[$i];
			}
		}
		ksort($graphSerie);
		return $graphSerie;
	}

    public function indexAction()
    {

    	$aggregationPipeline  = [
    		[ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
    		[ '$match'   => [ 'niceamount' => [ '$lt' => 0 ], 'nicedate' => [ '$gte' => new MongoDate( strtotime('first day of this month') ), '$lt' => new MongoDate( strtotime('last day of this month' ) ) ] ] ],
    		[ '$group'   => [ '_id' => '$date', 'total' => [ '$sum' => '$niceamount' ] ] ],
    		[ '$sort'    => [ '_id' => 1 ] ],
    		];
    	$currentMonthOperations = $this
			    					->get('doctrine_mongodb')
								    ->getManager()
								    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
								    ->aggregate( $aggregationPipeline );

    	$aggregationPipeline  = [
    		[ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
    		[ '$match'   => [ 'niceamount' => [ '$lt' => 0 ], 'nicedate' => [ '$gte' => new MongoDate( strtotime('first day of previous month') ), '$lt' => new MongoDate( strtotime('last day of previous month') ) ] ] ],
    		[ '$group'   => [ '_id' => '$date', 'total' => [ '$sum' => '$niceamount' ] ] ],
    		[ '$sort'    => [ '_id' => 1 ] ],
    		];
    	$previousMonthOperations = $this
			    					->get('doctrine_mongodb')
								    ->getManager()
								    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
								    ->aggregate( $aggregationPipeline );

    	$aggregationPipeline  = [
    		[ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
    		[ '$match'   => [ 'niceamount' => [ '$lt' => 0 ], 'nicedate' => [ '$gte' => new MongoDate( strtotime('first day of 2 month ago') ), '$lt' => new MongoDate( strtotime('last day of 2 month ago') ) ] ] ],
    		[ '$group'   => [ '_id' => '$date', 'total' => [ '$sum' => '$niceamount' ] ] ],
    		[ '$sort'    => [ '_id' => 1 ] ],
    		];
    	$twoMonthAgoOperations = $this
			    					->get('doctrine_mongodb')
								    ->getManager()
								    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
								    ->aggregate( $aggregationPipeline );

    	$aggregationPipeline  = [
    		[ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
    		[ '$match'   => [ 'niceamount' => [ '$lt' => 0 ], 'nicedate' => [ '$gte' => new MongoDate( strtotime('first day of 3 month ago') ), '$lt' => new MongoDate( strtotime('last day of 3 month ago') ) ] ] ],
    		[ '$group'   => [ '_id' => '$date', 'total' => [ '$sum' => '$niceamount' ] ] ],
    		[ '$sort'    => [ '_id' => 1 ] ],
    		];
    	$threeMonthAgoOperations = $this
			    					->get('doctrine_mongodb')
								    ->getManager()
								    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
								    ->aggregate( $aggregationPipeline );

    	$aggregationPipeline  = [
    		[ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
    		[ '$match'   => [ 'niceamount' => [ '$lt' => 0 ]] ],
    		[ '$group'   => [ '_id' => '$date', 'total' => [ '$sum' => '$niceamount' ] ] ],
    		[ '$sort'    => [ '_id' => 1 ] ],
    		];
    	$allMonthOperations = $this
			    					->get('doctrine_mongodb')
								    ->getManager()
								    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
								    ->aggregate( $aggregationPipeline );

        $series = array(
            array(
            	"name" => "Previous month",
            	"data" => $this->generateSerie( $previousMonthOperations )
            	),
            array(
            	"name" => "Current month",
            	"data" => $this->generateSerie( $currentMonthOperations )
            	),
            array(
            	"name" => "2 months ago",
            	"data" => $this->generateSerie( $twoMonthAgoOperations )
            	),
            array(
            	"name" => "3 months ago",
            	"data" => $this->generateSerie( $threeMonthAgoOperations )
            	),
            /*array(
            	"name" => "Average month",
            	"data" => $this->generateSerie( $allMonthOperations )
            	),*/
        );

    	// build graph
        $ob = new Highchart();
        $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
        $ob->title->text('Evolution du solde');
        $ob->xAxis->title(array('text'  => "Jour du mois"));
        $ob->xAxis->categories(range(1,31));
        $ob->yAxis->title(array('text'  => "Solde"));
        $ob->series($series);

    	// render
        return $this->render(
        	'ChmBankFollowUpBundle:Graph:index.html.twig', 
        	array(
        		//'operations' => $operationsCursor,
        		'chart' => $ob,
        		)
        	);
    }
}
