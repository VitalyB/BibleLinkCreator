<?php

/* Счетчик времени 
function timeMeasure() {
    list($msec, $sec) = explode(chr(32), microtime());
    return ($sec + $msec);
}
define('TIMESTART', timeMeasure()); /* */

include 'config.inc.php';			// конфигурационные данные
include 'bible_links_arrays.php'; 	// функции с массивами библейских ссылок
include 'test.php'; 				// тестовые данные

// Ин. 1:2-4,6; 7:8

define("SingleNode", 0); 	// цифра после запятой (6)
define("EndNode", 1); 		// цифра после тире (4)
define("RootNode", 2); 		// цифра после точки с запятой (7)
define("SubNode", 3);		// цифра после двоеточия (2, 8)
define("NamedNode", 4);		// самая первая цифра после имени книги (1)

class CNode 
{
    function __construct($type = SingleNode) {$this->SetType($type); $this->SetNumber(0);}

    public function SetType($type) {$this->m_nodeType = $type;}
    public function GetType() {return $this->m_nodeType;}

    public function SetNumber($n) {$this->m_num = $n;}
    public function GetNumber() {return $this->m_num;}

    private $m_nodeType;
    private $m_num;
}

class CNodeWrapper
{
    private $m_str;
    private $m_name;
	public  $m_pos;

    public function __construct($name, $str) {
        $this->m_name = $name;
        $this->m_str = $str;
		$this->m_pos = 0;
    }

    public function Parse(&$isFind) {
		$isFind = false;
	    $pos = 0;
        $node = new CNode();
		$pos = $this->TrimStr($pos, ".");
        $pos = $this->TrimStr($pos);
        $node->SetType(NamedNode);
		
		if(!$this->FillNode($node, $pos)) {
			return $this->m_name + " " + $this->m_str;
        }

        $curList = array();
        $curList[] = $node;
 
        $Nodes =  array();
        $Nodes[] = $curList;
        
        $res;
        do {
            $node1= new CNode();
            $res = $this->Parse_($node1, $pos);
			
           if ($res) {
                if ($node1->GetType() == SubNode) {
                    $endList= $Nodes[count($Nodes)-1];
                    if(count($endList) > 1) {
						array_pop($Nodes);
                        $endNode = $endList[count($endList)-1];
						array_pop($endList);
						array_pop($endList);
						$endList[] = $endNode;
                        $Nodes[] = $endList;
                    }
                } else if ($node1->GetType() == EndNode || $node1->GetType() == SingleNode) {
					array_pop($curList);
                } else if ($node1->GetType() == RootNode) {
					//$curList = array($curList[0],);
					$curList = array();
				}
				$curList[] = $node1;
                $Nodes[] = $curList;
            }

        } while ($res);
        $outPut;
        if (count($Nodes) > 0 && $this->WriteStr($Nodes, $outPut, $pos)) {
			$isFind = true;
            return $outPut;
        }
		return $this->m_name + " " + $this->m_str;
    }

	public function constructLink($name, &$nodeList) {
		
		global $doCorrection;
		global $source;
		global $translationAllBible;
		global $translationBibleOnline;
		global $translationBibleCenter;
		global $translationBibleServer;
		
		$books = GetBibleBooks($type = 'all');
		foreach ($books as $book => $index) {
			if (mb_strtolower($name, 'UTF-8') == mb_strtolower($book, 'UTF-8')) { // ucwords
				$bookIndex = $index;
				$name = $book;
				break;
			}
		}
		
		if ($nodeList[0][0]->GetType() == NamedNode) {
			$txtLink = ($doCorrection) ? RightBibleBooks($name) : $name;
		}
		
		for ($i = 0; $i < count($nodeList); $i++) {
			if (!$this->getNodeText($nodeList[$i], $txtLink)) {
               return "";
            }
		}
		
		// Формируем ссылку в зависимости от сайта
		// Адрес									перевод		книга				глава	стих
		// http://allbible.info/bible/				sinodal/	phm					/1#		5
		// http://bible.com.ua/bible/r/							57					/1#		5
		// http://biblezoom.ru/#								25					-1-		5
		// http://bibleonline.ru/bible/				rus/		64					/1/#	5
		// http://bible-center.ru/bibletext/		synnew_ru/	phm					/1#		phm1_5
		// http://bibleserver.com/text/				RUS/		Послание Филимону			5
		// http://bibleserver.com/text/				RUS/		Послание Римлянам 	1.		31
		
		switch ($source) {
			case 0:
				$link = 'http://allbible.info/bible/' . $translationAllBible . BibleIndexes($bookIndex, $source);
				break;
			case 1:
				$link = 'http://bible.com.ua/bible/r/' . $bookIndex;
				break;
			case 2:
				$link = 'http://biblezoom.ru/#' . BibleIndexes($bookIndex, $source);
				break;
			case 3:
				$link = 'http://bibleonline.ru/bible/' . $translationBibleOnline . BibleIndexes($bookIndex, $source);
				break;
			case 4:
				$link = 'http://bible-center.ru/bibletext/' . $translationBibleCenter . BibleIndexes($bookIndex, $source);
				break;
			case 5:
				$link = 'http://bibleserver.com/text/' . $translationBibleServer . BibleIndexes($bookIndex, $source);
				break;
			default:
				$link = 'http://allbible.info/bible/' . $translationAllBible . BibleIndexes($bookIndex, $source);
				break;
		}
		
		$nodeArray = $nodeList[0];
		$nodeChapter = $nodeArray[count($nodeArray)-1];
		$nodeArray = $nodeList[1];
		$node = $nodeArray[count($nodeArray)-1];
		if (IsSingleChapterBook($bookIndex) && count($nodeList) == 1) { // Учитывает одноглавные книги (Флм. 6 и Флм. 1:6)
			switch ($source) {
				case 2:
					$link .= '-1-' . $nodeChapter->GetNumber();
					break;
				case 3:
					$link .= '/1/#' . $nodeChapter->GetNumber();
					break;
				case 4:
					$link .= '/1#' . BibleIndexes($bookIndex, $source) . '1_' . $nodeChapter->GetNumber();
					break;
				case 5:
					$link .= $nodeChapter->GetNumber();
					break;						
				default:
					$link .= '/1#' . $nodeChapter->GetNumber();
					break;
			}
		} elseif (IsSingleChapterBook($bookIndex) && count($nodeList) > 1 && $source == 5 && $nodeChapter->GetNumber() == 1) {
			$link .= $node->GetNumber();
		} else {
			switch ($source) {
				case 2:
					$link .= '-' . $nodeChapter->GetNumber();
					break;
				case 5:
					$link .= $nodeChapter->GetNumber();
					break;						
				default:
					$link .= '/' . $nodeChapter->GetNumber();
					break;
			}
			if (count($nodeList) > 1) {
				if ($node->GetType() != 1) { 							// Учитывает интервал глав (Иов. 38–42)
					switch ($source) {
						case 2:
							$link .= '-' . $node->GetNumber();
							break;
						case 3:
							$link .= '/#' . $node->GetNumber();
							break;
						case 4:
							$link .= '#' . BibleIndexes($bookIndex, $source) . $nodeChapter->GetNumber() . '_' . $node->GetNumber();
							break;
						case 5:
							$link .= '.' . $node->GetNumber();
							break;
						default:
							$link .= '#' . $node->GetNumber();
							break;
					}
				}
			}
		}
		
		if ($nodeList[0][0]->GetType() == RootNode) {
			return "; <a href='$link' target='blank'>" . $txtLink . "</a>";
		} else {
			return "<a href='$link' target='blank'>" . $txtLink . "</a>";
		}
	}
	
    public function WriteStr(&$nodes, &$outPut, $pos)
    {
		$linkNodes = array();
        for ($i = 0; $i < count($nodes); $i++) {
            $beg = $nodes[$i];
            $txtLink = "";
			
			// define root nodes
			$bnode = $beg[count($beg)-1];
			if( $bnode->GetType() == RootNode) {
				if (count($linkNodes)) {
					$linkstr = $this->constructLink($this->m_name, $linkNodes);
					$outPut = $outPut . $linkstr;
				}
				$linkNodes = array();
			}
			$linkNodes[] = $beg;
        }
		if (count($linkNodes)) {
			$linkstr = $this->constructLink($this->m_name, $linkNodes);
			$outPut = $outPut . $linkstr;
		}

        if ($pos < strlen($this->m_str)) {
			$outPut = $outPut . substr($this->m_str, $pos);
        }
		$this->m_pos = $pos;
        return true;
    }
	
    public function getNodeText(&$nodeArray, &$txtLink) {
	
		global $СhapterSeparatorVerseOut;
		global $VerseSeparatorVerseOut;
	
		$node = $nodeArray[count($nodeArray)-1];
		switch ($node->GetType()) {
			case EndNode:
				$txtLink .= "&ndash;";
				break;
			case SubNode:
				$txtLink .= $СhapterSeparatorVerseOut;
				break;
			case RootNode:
				$txtLink .= ""; // Можно повторять название книги каждый раз, при указании новой главы
				break;
			case SingleNode:
				$txtLink .= $VerseSeparatorVerseOut;
				break;
			case NamedNode:
				$txtLink .= "&nbsp;";
				break;
		}
		$txtLink .= $nodeArray[count($nodeArray)-1]->GetNumber();

        return true;
    }

    public function Parse_(&$node, &$pos) {

		global $СhapterSeparatorVerseIn;
		global $VerseSeparatorVerseIn;
	
		$oldPos = $pos;
        $pos = $this->TrimStr($pos);
		$pos++;
        switch ($this->m_str[$pos-1]) {
        case '-':
                $node->SetType(EndNode);
                break;
        case $СhapterSeparatorVerseIn:
                $node->SetType(SubNode);
                break;
        case $VerseSeparatorVerseIn:
               $node->SetType(SingleNode);
               break;
        case ';':
               $node->SetType(RootNode);
               break;
        default:
			// поиск среднего и длинного тире в UTF-8, &ndash; и &mdash;
			if (ord($this->m_str[$pos-1]) == 226 && ord($this->m_str[$pos]) == 128 
				&& (ord($this->m_str[$pos+1]) == 147 || ord($this->m_str[$pos+1]) == 148)) {
				$pos++;
				$pos++;
				$node->SetType(EndNode);
                break;
			} elseif ($this->m_str[$pos-1] == '&' && ($this->m_str[$pos] == 'n' || $this->m_str[$pos] == 'm')
				&& $this->m_str[$pos+1] == 'd' && $this->m_str[$pos+2] == 'a' && $this->m_str[$pos+3] == 's' 
				&& $this->m_str[$pos+4] == 'h' && $this->m_str[$pos+5] == ';') {
				$pos += 6;
				$node->SetType(EndNode);
				break;
			} else {
            //$pos--;
			$pos = $oldPos;
            return false;
			}
        }
		if ($this->FillNode($node, $pos)) {
			return true;
		}
		//$pos--;
		$pos = $oldPos;
        return false;
    }

    public function  TrimStr($pos, $trimChar = "") {
		
		$str = substr($this->m_str, $pos);
		$currentSize = strlen($str);
		if ($trimChar == ".") {
			$str = ltrim($str, $trimChar);
		} else {
			$str = preg_replace('/^(&nbsp;| )+/', '', $str);
		}
		return $pos + ($currentSize - strlen($str));
    }

    public function GetInt(&$pos, &$n) {
		$str = substr($this->m_str, $pos);
		$h = sscanf($str, "%d", $n);
		if ($h != 1) {
			return false;
		}
		$pos = $pos + strlen((string)$n);
		return true;
    }

    // read number from str and set in node
    public function FillNode(&$none, &$pos) {
        $n;
        $pos = $this->TrimStr($pos);
       
		if ($this->GetInt($pos, $n)) {
            $none->SetNumber($n);
        } else {
			return false;
        }
        return true;
    }
}

// Разбивка строки на части, где каждая часть содержит одну именнованую книгу
// $str - входная строка, $v - выходной вектор подстрок, $vNames - выходной вектор имен книг

/*function CreateLinkBlocks(&$str, &$v, &$vNames) {
	
	$books = GetBibleBooks($type = 'all');
	$pos = 0;
	$posBegin = false;
	$name = "";
	$ifFind = false;
	$posToReturn = 0;
	
	do {
		$ifFind = false;
		$minPos = false;
		$minPosBook = "";
		$strLower = mb_strtolower($str, 'UTF-8'); // Перед поиском строки приводятся к нижнему регистру
		$prefixes = array(". ", ".", "&nbsp;", ".&nbsp;", " ", "");
		$minpositions = array();
		$bookpositions = array();
		foreach ($prefixes as $prefix) {
			foreach ($books as $book => $index) {
				$finder = mb_strtolower($book, 'UTF-8') . $prefix; // ucwords
				
				$posBegin = strpos($strLower, $finder, $pos); // stripos и mb_stripos не отрабатывают
			
				if ($posBegin !== false) {
					$tempName = substr($str, $posBegin , strlen($finder) - strlen($prefix));
					if ($minPos === false) {
						$minPos = $posBegin;
						$minPosBook = $tempName;
					} else if ($minPos > $posBegin || ($minPos == $posBegin && strlen($tempName) > strlen($minPosBook))) {
						$minPos = $posBegin;
						$minPosBook = $tempName;
					}
				}
			}
			$minpositions[] = $minPos;
			$bookpositions[] = $minPosBook;
		}
		$posBegin = $minPos;
		$posBook = $minPosBook;
		foreach($minpositions as $key=>$minposition) {
			if ($minposition < $posBegin) {
				$posBegin = $minposition;
				$posBook = $bookpositions[$key];
			}
		}
		if ($posBegin !== false) {
			if ($pos > 0) {
				$v[] = substr($str, $pos, $posBegin - $pos);
				$vNames[] = $name;
			} else {
				$posToReturn = $posBegin;
			}
			$name = $posBook;
			$pos = $posBegin + strlen($posBook);
			$ifFind = true;
		}
		
	} while ($ifFind);

	if ($name) {
		$v[] = substr($str, $pos, strlen($str) - $pos);
		$vNames[] = $name;
	}
	
    return $posToReturn;
} */

function GetNextBook($content, $posbegin, &$posofbook, &$bookname) {
	$books = GetBibleBooks($type = 'all');
	$currentpos = $posbegin;
	$bookname = false;
	$maxpos = strlen($content) - 1;
	$contentLower = mb_strtolower($content, 'UTF-8'); // приведение к нижнему регистру
	
	// пришлось оптимизировать этот скрипт, чтобы выполнять mb_strtolower один раз,
	// а то скороть выполнения скрипта падает на порядок!
	// все книги в массивах теперь в нижнем регистре
	while ($currentpos < $maxpos) {
		foreach ($books as $book => $index) {
			$tempBookNameLower = substr($contentLower, $currentpos, strlen($book));
			if ($tempBookNameLower == $book and strlen($tempBookNameLower) > strlen($bookname)) {
				$tempBookName = substr($content, $currentpos, strlen($book));
				$bookname = $tempBookName;
			}
		}
		if ($bookname) {
			$posofbook = $currentpos;
			return true;
		}
		$currentpos++;
	}
	return false;
}

function SearchBibleLinks($content) {
    /*$v;
	$vNames;
    $pos = CreateLinkBlocks($str, $v, $vNames);
    $output = substr($str, 0, $pos);
	
    for ($i=0; $i < count($v); $i++){
		$w = new CNodeWrapper($vNames[$i], $v[$i]);
		$t = $w->Parse();
		if ($t) {
			$output = $output . $t;
		} else {
			//$output = $output . "<b>" . $vNames[$i] . $v[$i] . "</b>" ;
			$output = $output . $vNames[$i] . $v[$i] ;
		}
    }*/
	
	while (GetNextBook($content, 0, $posofbook, $bookname)) {
		$w = new CNodeWrapper($bookname, substr($content, $posofbook + strlen($bookname)));
		$t = $w->Parse($isfind);
		
		if ($isfind) {
			$endOfLink = $posofbook + strlen($bookname) + $w->m_pos; // $w->m_pos - len of text link
			$contentlen = strlen($content) - $endOfLink; 
			$linklen = strlen($t) - $contentlen;
			
			$output .= substr($content, 0, $posofbook) . substr($t, 0, $linklen);
			$content = substr($content, $endOfLink);
		} else {
			$output .= substr($content, 0, $posofbook) . $bookname;
			$content = substr($content, $posofbook + strlen($bookname));
		}
	}
	$output .= $content;
	
	return $output;
}

// add_filter('the_content', 'SearchBibleLinks');

echo $str.'<br/>'.SearchBibleLinks($str).'<br/>';	
//echo 'Страница сгенерировалась за '.round(timeMeasure() - TIMESTART, 4).' сек.';
?>