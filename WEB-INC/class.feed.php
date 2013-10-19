﻿<?php
// класс обаботки xml-файла
class feed {
	/**
	* Обрработка XML-документа, преобразование в массив
	*
	* Param: str $strXmlDoc
	* Return: array
	*/
	function parse($strXmlDoc) {
		$boolDocument = false;
		$arrRssData = null;

		$p = xml_parser_create();
		xml_parse_into_struct($p, $strXmlDoc, $vals, $index);
		xml_parser_free($p);

		$type = 0;
		$tmp[] = array("", "", "");
		$id = 0;

		for ($i = 0, $cvals = count($vals); $i < $cvals; ++$i) {
			if (($vals[$i]['tag'] == "RSS") && ($vals[$i]['type'] == "open")) {
				switch ($vals[$i]['attributes']['VERSION']) {
					case "0.91" : break;
					case "1.0" : break;
					case "2.0" :
						// title
						// link
						// description
						// language
						// pubDate
						break;
				}

				$boolDocument = true;
			}
		}
		
		if($boolDocument === false) {
			return false;
		}
		
		for ($i = 0, $cvals = count($vals); $i < $cvals; ++$i) {
			if (($vals[$i]['tag'] == "CHANNEL") && ($vals[$i]['type'] == "open")) {
				$id = $vals[$i]['level'] + 1;
			}

			if (($type == 0) && ($id == $vals[$i]['level'])) {
				switch ($vals[$i]['tag']) {
					case "TITLE" :
						$channel['TITLE'] = addslashes($vals[$i]['value']);
					break;
					case "LINK" :
						$channel['LINK'] = $vals[$i]['value'];
					break;
					case "DESCRIPTION" :
						$channel['DESCRIPTION'] = addslashes($vals[$i]['value']);
					break;

					// ----------
					case "LANGUAGE" :
						$channel['LANGUAGE'] = $vals[$i]['value'];
					break; 
					case "COPYRIGHT" :
						$channel['COPYRIGHT'] = $vals[$i]['value'];
					break;					
					case "MANAGINGEDITOR" :
						$channel['MANAGINGEDITOR'] = $vals[$i]['value'];
					break;
					case "WEBMASTER" :
						$channel['WEBMASTER'] = $vals[$i]['value'];
					break;
					case "PUBDATE" :
						$channel['PUBDATE'] = $vals[$i]['value'];
					break;
					case "LASTBUILDDATE" :
						$channel['LASTBUILDDATE'] = $vals[$i]['value'];
					break;
					case "CATEGORY" :
						$channel['CATEGORY'] = $vals[$i]['value'];
					break;
					case "GENERATOR" :
						$channel['GENERATOR'] = $vals[$i]['value'];
					break;
					case "DOCS" :
						$channel['DOCS'] = $vals[$i]['value'];
					break;
					case "CLOUD" :
						$channel['CLOUD'] = $vals[$i]['value'];
					break;
					case "TTL" :
						$channel['TTL'] = $vals[$i]['value'];
					break;
					
					case "IMAGE" :
						$ci = $i + 1;

						if ($vals[$ci]['tag'] == "URL" && ($vals[$ci]['level'] == ($vals[$i]['level'] + 1))) {
							$image['URL'] = $vals[$ci]['value'];
							$i++;
						}
						if ($vals[$ci]['tag'] == "LINK" && ($vals[$ci]['level'] == ($vals[$i]['level'] + 1))) {
							$image['LINK'] = $vals[$ci]['value'];
							$i++;
						}
						if ($vals[$ci]['tag'] == "TITLE" && ($vals[$ci]['level'] == ($vals[$i]['level'] + 1))) {
							$image['TITLE'] = addslashes($vals[$ci]['value']);
							$i++;
						}
					break;
				}
			} else {
				switch ($vals[$i]['tag']) {
					case "TITLE":
						$tmp["TITLE"] = addslashes($vals[$i]['value']);
					break;
					case "LINK" :
						$tmp["LINK"] = $vals[$i]['value'];
					break;
					case "DESCRIPTION" :
						$tmp['DESCRIPTION'] = addslashes($vals[$i]['value']);
						break;
					case "AUTHOR" :
						$tmp['AUTHOR'] = $vals[$i]['value'];
					break;
					case "CATEGORY" :
						$tmp['CATEGORY'] = $vals[$i]['value'];
					break;
					case "COMMENTS" :
						$tmp['COMMENTS'] = addslashes($vals[$i]['value']);
					break;
					case "ENCLOUSURE" :
						$tmp['ENCLOUSURE'] = $vals[$i]['value'];
					break;
					case "GUID" :
						$tmp['GUID'] = $vals[$i]['value'];
					break;
					case "PUBDATE" :
						$tmp['PUBDATE'] = $vals[$i]['value'];
					break;
					case "SOURCE" :
						$tmp['SOURCE'] = $vals[$i]['value'];
					break;
				}
			}

			if ($vals[$i]['tag'] == "ITEM") {
				if (($vals[$i]['type'] == "open") && ($type == 0)) {
					$type = 1;
				}

				if ($vals[$i]['type'] == "close") {
					//$items[] = new container_item(null, null, $tmp['PUBDATE'], $tmp["TITLE"], $tmp["LINK"], $tmp['DESCRIPTION'], $tmp['AUTHOR'], $tmp['CATEGORY'], $tmp['COMMENTS'], $tmp['ENCLOUSURE'], $tmp['GUID'], $tmp['PUBDATE'], $tmp['SOURCE']);
					$items[] = new container_item(null, null, (date("Ymdhis", strtotime($tmp['PUBDATE']))), $tmp["TITLE"], $tmp["LINK"], $tmp['DESCRIPTION'], $tmp['AUTHOR'], $tmp['CATEGORY'], $tmp['COMMENTS'], $tmp['ENCLOUSURE'], $tmp['GUID'], $tmp['PUBDATE'], $tmp['SOURCE']);
					unset($tmp);
				}
			}
		}

		//$arrRssData['feed'] = new container_feed(null, null, null, (date("Ymdhis", strtotime($channel['LASTBUILDDATE']))), (date("Ymdhis", strtotime($channel['PUBDATE']))), null, $channel['TITLE'], $channel['LINK'], $channel['DESCRIPTION'],
		$arrRssData['feed'] = new container_feed(null, null, null, $channel['LASTBUILDDATE'], $channel['PUBDATE'], null, $channel['TITLE'], $channel['LINK'], $channel['DESCRIPTION'],
		$channel['LANGUAGE'], $channel['COPYRIGHT'], $channel['MANAGINGEDITOR'], $channel['WEBMASTER'],
		$channel['PUBDATE'], $channel['LASTBUILDDATE'], $channel['CATEGORY'], $channel['GENERATOR'],
		$channel['DOCS'], $channel['CLOUD'], $channel['TTL'], $image['URL'], $image['TITLE'], $image['LINK']);

		$arrRssData['items'] = $items;

		return $arrRssData;
	} // end function getrss
	
	

	
	public function updateIndexDate($intFeedID) {
		$t = date("Ymdhis");
		mysql_query("UPDATE `feed_feeds` SET `lastindex`='{$t}' WHERE (`feed_id`={$intFeedID})");
		
		echo "\n-----\n".mysql_error()."\n-----\n";
		
		if (mysql_errno()) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Выводит массив с указанным кол-вом RSS-лент,
	 * индексация которых производилась давно
	 * @param int $num
	 * @return array 
	 */
	public function getLongTimeIndex($num = 1) {
		$tmp->sql = "SELECT * FROM `feed_feeds` ORDER BY `lastindex` ASC LIMIT {$num}";
		$tmp->query = mysql_query($tmp->sql);
		$tmp->num = mysql_num_rows($tmp->query);
		$tmp->res = null;

		if ($tmp->num == 0) {
			return null;
		}
		
		while ($r = mysql_fetch_object($tmp->query)) {
			$tmp->res[] = $r;
		}
		
		return $tmp->res;
	}
};