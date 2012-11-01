<?php

class DBx_Processor_Mysql extends DBx_Processor
{
        // this function should be commonly used in creating mysql-dumps
	// it gets some symbols from sql-string, skipping comments
	// and screened symbols in quotations
	function next($s, $pos = 0)
	{

		$ch = $this->_ch($s, $pos);
		$len = strlen($s);
		$result = $pos;
		while ($len > $result && preg_match('/^\s$/', $ch)) {
			$result++;
			if (preg_match('/\S$/', $this->expr))
				$this->expr .= ' ';
			else if ($ch == "\n")
				$this->expr .= "\n";
			$ch = $this->_ch($s, $result);
		}
		if (strstr('+*&|^%!@/=,[]{}', $ch)) {
			$this->expr .= $ch;
			return $result;
		}

		if ($ch == '\'') {
			$this->expr .= $ch;
			do {
				$result++;
				if ($this->_ch($s, $result) == '\\') {
					$result++;
					$this->expr .= $this->_ch($s, $result);
				}
				$this->expr .= $this->_ch($s, $result);
			} while ($len > $result &&
			$this->_ch($s, $result) != '\'');
		} else if ($ch == '"') {
			$this->expr .= $ch;
			do {
				$result++;
				if ($this->_ch($s, $result) == '\\') {
					$result++;
					$this->expr .= $this->_ch($s, $result);
				}
				$this->expr .= $this->_ch($s, $result);
			} while ($len > $result &&
			$this->_ch($s, $result) != '"');
		} else if ($ch == '#' ||
				( $ch == '/' && $this->_ch($s, $result + 1) == '/' ) ||
				( $ch == '-' && $this->_ch($s, $result + 1) == '-' )) {

			$comment = '';
			if ($ch != '#')
				$result++;
			do {
				$result++;
			} while ($len > $result && $this->_ch($s, $result) != "\n" && $this->_ch($s, $result) != "\r");
		} else {
			$this->expr .= $ch;
		}
		return $result;
	}
}