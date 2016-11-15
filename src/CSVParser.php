<?php namespace KenBarbour\Util\CSVParser;

class CSVParser
{
    /**
     * Input stream to fetch data from
     * @var
     */
    protected $input;

    /**
     * If true, treat the first line as a header that contains field names rather than data.
     * @var bool
     */
    protected $has_header;

    /**
     * Set to true when header has been read
     * @var bool
     */
    protected $has_read_header = false;

    protected $headings_map;

    /**
     * Optional array used in order to be flexible when matching field names found in the header line.  If no header
     * line is provided, this field will not be used.
     * @var array
     */
    protected $alt_headings;

    protected $cursor;

    protected $data;

    public function __construct($input = null)
    {
        $this->input = $input;
    }

    public function fromFile($file)
    {
        $fp = fopen($file,'r');
        $this->input = $fp;

        return $this;
    }

    public function fromString($string)
    {
        $fp = fopen('data://text/plain,'.$string,'r');
        return $this->fromStream($fp);
    }

    public function fromStream($stream) {
        $this->input = $stream;
        return $this;
    }

    public function hasHeader($has_header = true)
    {
        $this->has_header = ($has_header) ? true : false;
        return $this;
    }

    public function addAlternativeHeading($real, $alt)
    {
        if (!is_array($this->alt_headings))
            $this->alt_headings = array();
        if (!array_key_exists($real,$this->alt_headings))
            $this->alt_headings[$real] = array();
        $this->alt_headings[$real][]=$alt;
        return $this;
    }

    public function altHeading($real, $alt)
    {
        return $this->addAlternativeHeading($real, $alt);
    }

    /**
     * Fetches the next row in the CSV data, or false if no data
     * @return array|false
     */
    protected function getLine()
    {
        $line = fgetcsv($this->input);
        return $line;
    }

    public function fetch()
    {
        $line = $this->getLine();
        if (!$line) return false;
        if ($this->has_header && !$this->has_read_header) {
            $this->has_read_header = true;
            $this->map_headings($line);
            return $this->fetch();
        }
        $data = array();
        foreach ($line as $key => $value) {
            $data[$this->get_heading_at_index($key)] = $value;
        }
        return $data;
    }

    protected function map_headings($headings)
    {
        $map = array();
        foreach ($headings as $key => $heading) {
            $map[$key] = $this->get_alternative_heading($heading);
        }
        $this->headings_map = $map;
    }

    protected function get_heading_at_index($index)
    {
        if (isset($this->headings_map[$index]))
            return $this->headings_map[$index];
        return $index;
    }

    protected function get_alternative_heading($heading)
    {
        if (!empty($this->alt_headings)) {
            foreach ($this->alt_headings as $real => $alternates) {
                foreach ($alternates as $alternate)
                    if ($heading == $alternate)
                        return $real;
            }
        }
        return $heading;
    }
}