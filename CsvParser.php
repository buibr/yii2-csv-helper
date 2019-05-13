<?php

namespace buibr\csvhelper;

/**
 * This is just an example.
 */
class CsvParser
{
    /**
     * This is the file from where we get data.
     */
    public $file;

    /**
     * This is the data where we put all procesed records
     */
    protected $data;

    /**
     * Save all headers on this object
     */
    protected $headers;

    /**
     * Seperator of columns.
     */
    protected $delimeter = ',';

    /**
     * "quote" - quote all values with duble quotes on output
     */
    protected $enclosure = false;

    /**
     * Convert all to utf8
     */
    protected $utf8 = false;


    /**
     *  Parse data from file provided here or from class instantance
     * @param stirng $file  the file to parse from.
     * @return CsvParser $this
     */
    public function fromFile( $file = null )
    {
        if(empty($file) && empty($this->file))
        {
            throw new \ErrorException("File is not defined.");
        }

        $this->file = $file ? $file : $this->file;

        if(!\is_readable($this->file))
        {
            throw new \ErrorException("File is not found");
        }

        $data = \file($this->file);

        if(empty($data))
        {
            throw new \ErrorException("Empty file uploaded.");
        }

        foreach($data as $row){

            //  remove not utf characters.
            $content        = @iconv("UTF-8", "UTF-8//IGNORE", $row);

            //  to array
            $this->data[]   = \str_getcsv($content, $this->delimeter, $this->enclosure);
        }

        //  
        $this->headers      = $this->data[0];

        //  remove the firs element as its headers
        @\array_shift($this->data);

        return $this;
    }

    /** 
     * @param stirng $data  the file to parse from.
     * @return CsvParser $this
     */
    public function fromData( array &$data = null ){
        if(empty($data) && empty($this->data))
        {
            throw new \ErrorException("Data is not set.");
        }

        $this->data     = $data ? $data : $this->data;
        $first          = current($this->data);
        $this->headers  = \array_keys($first);

        return $this;
    }

    /**
     *  Parse data from array to this data.
     *  
     * Example
     * ```php 
     * $arr = [
     *      [
     *          "name"=>"burhan",
     *          "sname"=>"ibraimi",
     *      ],
     *      [
     *          "name"=>"test",
     *          "sname"=>"test",
     *      ]
     * ];
     * ```
     * 
     * @param stirng $data  the file to parse from.
     * @return CsvParser $this
     */
    public function fromArray( array &$data )
    {
        if(empty($data) && empty($this->data))
        {
            throw new \ErrorException("Data is not set.");
        }

        // get headers from 
        $this->headers  = \array_values(current($data));

        foreach($data as &$v){
            $this->data[] = \array_values($v);
        }

        //  remove the firs element as its headers
        @\array_shift($this->data);

        return $this;
    }

    /**
     *  Parse data from string or content
     * @param stirng $data  the file to parse from.
     * @return CsvParser $this
     */
    public function fromContent( string &$content )
    {
        if(empty($content)) {
            throw new \ErrorException("Data is not set.");
        }

        $content        = @iconv("UTF-8", "UTF-8//IGNORE", $content);
        $content        = explode("\n", $content);

        foreach($content as $row){
            $this->data[] = str_getcsv($row);
        }

        //  seperate headers from data.
        $first          = current($this->data);
        $this->headers  = \array_values($first);

        //  remove the firs element as its headers
        @\array_shift($this->data);

        return $this;
    }

    /**
     * Parse full object to arrays with attached headers to each row.
     * @return array $data;
     */
    public function toArray()
    {
        $arr = [];
        foreach($this->data as &$row)
        {
            //  attach keys to object.
            $arr[]   = array_combine($this->headers, $row);
            
        }

        return $arr;
    }

    /** 
     * Parse all elements and return only one column as specified if exists.
     * @param string $colum - the column to be return as single array
     * @return array 
     */
    public function toColumn( $column = null )
    {
        if(empty($column)){
            throw new \ErrorException("Column not specified.");
        }

        //
        $position = \array_search(trim($column), $this->headers);

        // if(!\in_array( trim($column), $this->headers)){
        if(is_null($position) ) {
            throw new \ErrorException("This '{$column}' column is not found in headers");
        }

        $return = [];
        foreach($this->data as &$row)
        {
            $return[]   = $row[$position];
        }

        return $return;

    }

    /**
     * Parse all elements and return only one column as specified  as key in array filled with $value.
     * @param string $colum - the column to be return as single array
     * @return array 
     */
    public function toColumnFill( $column = null, $value = null)
    {
        if(empty($column)){
            throw new \ErrorException("Column not specified.");
        }

        $position = \array_search(trim($column), $this->headers);

        // if(!\in_array( trim($column), $this->headers)){
        if( is_null($position) ) {
            throw new \ErrorException("This '{$column}' column is not found in headers");
        }

        $return = [];
        foreach($this->data as $id=>&$row)
        {
            //  to array
            $row                        = \str_getcsv($row, $this->delimeter, $this->enclosure);
            $return[$row[$position]]    = $value;
        }

        return $return;

    }

    /**
     * Rebuild csv as content for download or print in raw.
     * @param string $colum - the column to be return as single array
     * @return array 
     */
    public function toContent()
    {
        $content = implode($this->delimeter, $this->headers). "\n";

        foreach($this->data as &$row)
        {
            $content .= implode($this->delimeter, $row) . "\n";
        }

        return $content;
    }

}