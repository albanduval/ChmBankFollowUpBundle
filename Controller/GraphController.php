<?php

namespace Chm\BankFollowUpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ob\HighchartsBundle\Highcharts\Highchart;
use \DateTime;
use \MongoDate;

class GraphController extends Controller
{
    private function generateSerie ( $mongoResult, $currentMonth = false ) {
        $start = 2800;
        $previousValue = $total = $start;
        $graphSerie = [];
        foreach($mongoResult as $element) {
            $total += $element['total'];
            $date = explode('/', $element['_id']);
            $day = (int)$date[0];
            $graphSerie[$day] = $total;
        }
        $curDay = date('d');
        // highchart seems to require all x values to have a valid value, so this is required to fill in the blanks
        for($i=0;$i<=31;$i++) {
            if( $currentMonth && $i >= $curDay ) {
                break;
            } elseif( !array_key_exists($i, $graphSerie)) {
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
        $series = [];

        $averageLength = 12;
        $aggregationPipeline  = [
            [ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
            [ '$match'   => [ 'niceamount' => [ '$lt' => 0 ], 'nicedate' => [ '$gte' => new MongoDate( strtotime('first day of ' . ( $averageLength + 1 ) . ' month ago') ), '$lt' => new MongoDate( strtotime('last day of 1 month ago') ) ] ] ],
            //[ '$group'   => [ '_id' => '$date', 'nicedate' => [ '$first' => '$nicedate' ], 'total' => [ '$sum' => '$niceamount' ] ] ],
            //[ '$group'   => [ '_id' => [ '$dayOfMonth' => '$nicedate' ], 'total' => [ '$divide' => ['$total', $averageLength] ] ] ],
            [ '$group'   => [ '_id' => [ '$dayOfMonth' => '$nicedate' ], 'nicedate' => [ '$first' => '$nicedate' ], 'total' => [ '$sum' => '$niceamount' ] ] ],
            [ '$project' => [ '_id' => 1, 'total' => [ '$divide' => ['$total', ($averageLength+1)] ] ] ],
            [ '$sort'    => [ '_id' => 1 ] ],
            ];
        $allMonthOperations = $this
                                    ->get('doctrine_mongodb')
                                    ->getManager()
                                    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
                                    ->aggregate( $aggregationPipeline );

        $serie = [
                "name"      => "Average of last $averageLength months",
                "data"      => $this->generateSerie( $allMonthOperations ),
                'lineWidth' => 1,
                'type'      => 'spline',
                'dashStyle' => 'longdash',
                ];
        array_push( $series, $serie );

        $pastMonthToHandle = 5;
        for($i=1;$i<=$pastMonthToHandle;$i++) {
            $aggregationPipeline  = [
                [ '$project' => [ 'date' => 1, 'nicedate' => 1, 'niceamount' => 1 ] ],
                [ '$match'   => [ 'niceamount' => [ '$lt' => 0 ], 'nicedate' => [ '$gte' => new MongoDate( strtotime('first day of ' . $i . ' month ago') ), '$lt' => new MongoDate( strtotime('last day of ' . $i . ' month ago') ) ] ] ],
                [ '$group'   => [ '_id' => '$date', 'total' => [ '$sum' => '$niceamount' ] ] ],
                [ '$sort'    => [ '_id' => 1 ] ],
                ];
            $singleMonthOperations = $this
                                    ->get('doctrine_mongodb')
                                    ->getManager()
                                    ->getDocumentCollection('ChmBankFollowUpBundle:Operation')
                                    ->aggregate( $aggregationPipeline );
            $serie = [
                "name" => date('M Y', strtotime("$i month ago")),
                "data" => $this->generateSerie( $singleMonthOperations ),
                'lineWidth' => 1,
                'dashStyle' => 'shortdot',
                'visible' => false,
                'type' => 'spline',
                ];
            array_push( $series, $serie );
        }

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
        $serie = [
            "name"      => "Current month",
            "data"      => $this->generateSerie( $currentMonthOperations, true ),
            'lineWidth' => 3,
            'type'      => 'spline',
            'color'     => '#E82C0C',
            ];
        array_push( $series, $serie );

        // build graph
        $ob = new Highchart();
        $ob->chart->renderTo('linechart');  // The #id of the div where to render the chart
        $ob->title->text('Evolution du solde');
        $ob->xAxis->title(array('text'  => "Jour du mois"));
        $ob->xAxis->categories(range(1,31));
        $ob->yAxis->title(array('text'  => "Solde"));
        $ob->yAxis->plotBands( [
            'color' => '#FFE389', // Color value
            'from'  => '-100000',   // Start of the plot band
            'to'    => '0',       // End of the plot band
            ]);
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
