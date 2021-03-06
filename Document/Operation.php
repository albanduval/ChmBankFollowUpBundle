<?php
namespace Chm\BankFollowUpBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="operations")
 */
class Operation
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $date;

    /**
     * @MongoDB\Date
     */
    protected $nicedate;

    /**
     * @MongoDB\String
     */
    protected $label;

    /**
     * @MongoDB\String
     */
    protected $category;

    /**
     * @MongoDB\Float
     */
    protected $amount;

    /**
     * @MongoDB\String
     */
    protected $notes;

    /**
     * @MongoDB\String
     */
    protected $check_number;

    /**
     * @MongoDB\String
     */
    protected $tags;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param date $date
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get label
     *
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return self
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return string $category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return self
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * Get notes
     *
     * @return string $notes
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set checkNumber
     *
     * @param string $checkNumber
     * @return self
     */
    public function setCheckNumber($checkNumber)
    {
        $this->check_number = $checkNumber;
        return $this;
    }

    /**
     * Get checkNumber
     *
     * @return string $checkNumber
     */
    public function getCheckNumber()
    {
        return $this->check_number;
    }

    /**
     * Set tags
     *
     * @param string $tags
     * @return self
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Get tags
     *
     * @return string $tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set nicedate
     *
     * @param date $nicedate
     * @return self
     */
    public function setNicedate($nicedate)
    {
        $this->nicedate = $nicedate;
        return $this;
    }

    /**
     * Get nicedate
     *
     * @return date $nicedate
     */
    public function getNicedate()
    {
        return $this->nicedate;
    }
}
