<?php
namespace FragTale\Controller;
use FragTale\Controller;
use FragTale\CMS\View_Content_Search;
use FragTale\CMS\Article;

/**
 * @author fabrice
 * 
 * This is the main content search stuff. It is a little complicated since it uses a MySQL view ("view_content_search")
 * and this MySQL view required a function "fnStripTags" that is needed to strip html tags from html content data searched.
 * This function is not a native MySQL function and you must import it from the SQL script placed in "apps/models/FragTale/cms".
 * 
 * The search stuff follow a certain logical way to render the results.
 * First, and of course, there is a search box block that submit a form by "GET" method (but it can be set to "POST").
 * I think that it's better to pass the arguments into URL for SEO.
 * 2nd, the following code concatenate a multiple SQL statements with "UNION"
 * 3rd, I have a particular way to return the string results (tag stripped), knowing that the database include html special chars
 * 	that complicates the search of terms with accent.
 * 
 * So, the search is sensitive for accent, but it can match the accent encoded in html code. The search is not sensitive for
 * upper/lower cases.
 * 
 * On top of that, the results are prioritized with those main criterias (but there are some others):
 * 	1. articles containing the full sentence search:
 *    a. into the article title
 *    b. into the article body
 *  2. articles containing all the terms somewhere in the text, no matter the order of appearance
 *  3. articles containing at least one of the terms
 *  
 *  And for each of these criterias, articles are ordered by creation date from the later to the earlier.
 * 
 */
class Search extends Controller{
	
	function initialize(){
		$this->setTitle(_('Search result'));
		$this->_view->terms			= '';
		$this->_view->search_results= array();
		$this->_view->addJS(WEB_ROOT.'/js/search.js');
	}
	
	function main(){
		$terms = array();
		if (!empty($_REQUEST['terms'])){
			$this->_view->terms = trim($_REQUEST['terms']);
			if (strlen($this->_view->terms)<2)
				return false;
			foreach (explode(' ', str_replace('"', '', $this->_view->terms)) as $term)
				if (!empty($term))
					$terms[] = $term;
		}
		if (empty($terms)) return false;
		/*** The search use a sql view that already prioritizes the order of the results ***/
		$View_Content_Search = new View_Content_Search();
		$Article	= new Article();
		$conditions = array();
		$baseSearch = implode(' ', $terms);
		$selectSearchResults = array();
		foreach ($terms as $term){
			if (strlen($term)<2) continue;
			foreach (array(str_replace("'", "''", $term), htmlentities($term, ENT_QUOTES, 'UTF-8')) as $tmpTerm)
				$selectSearchResults[] =
					" CASE WHEN LOCATE('$tmpTerm', content)=0 THEN %s ELSE
						CASE WHEN LOCATE('$tmpTerm', content)>=30 THEN SUBSTRING(content, LOCATE('$tmpTerm', content)-30, 120) ELSE
							SUBSTRING(content, 1, 120) END END ";
		}
		$selectSearchResult = '';
		foreach ($selectSearchResults as $sqlPart){
			if (empty($selectSearchResult))
				$selectSearchResult = $sqlPart;
			else
				$selectSearchResult = sprintf($selectSearchResult, $sqlPart);
		}
		$selectSearchResult = sprintf($selectSearchResult, "''");
		$baseSqlStatement =
			'SELECT '.
				'%d AS top_priority,'.
				'result_strength,'.
				'aid,'.
				'uid,'.
				'catid,'.
				'request_uri,'.
				'article_title,'.
				"$selectSearchResult as search_result,".
				'cre_date,'.
				'publish '.
			'FROM '.$View_Content_Search->getFullTableName().' WHERE ';
		$baseCondition = "REPLACE(REPLACE(LOWER(content), '&nbsp;', ' '), '&#39;', '&#039') LIKE '%{}%' COLLATE utf8_bin";
		$baseOrder	= 'top_priority ASC, result_strength ASC, cre_date DESC';
		//1: search the exact sentence in: a-> normal string, b-> html entities
		$conditions['base']			= str_replace('{}', str_replace("'", "''", strtolower($baseSearch)), $baseCondition);
		$conditions['baseinhtml']	= str_replace('{}', htmlentities(strtolower($baseSearch), ENT_QUOTES, 'UTF-8'), $baseCondition);
		//2: search content having all of the terms but in different places: a-> normal string, b-> html entities
		//i.e concat with "AND"
		$conditions['andbase'] = $conditions['andbasehtml'] = '';
		foreach ($terms as $term){
			if (!empty($conditions['andbase'])){
				$conditions['andbase']		.= ' AND ';
				$conditions['andbasehtml']	.= ' AND ';
			}
			$conditions['andbase']		.= str_replace('{}', str_replace("'", "''", strtolower($term)), $baseCondition);
			$conditions['andbasehtml']	.= str_replace('{}',  htmlentities(strtolower($term), ENT_QUOTES, 'UTF-8'), $baseCondition);
		}
		//3: search content having at least one of the terms: a-> normal string, b-> html entities
		$conditions['orbase'] = $conditions['orbasehtml'] = '';
		foreach ($terms as $term){
			if (strlen($term)<3) continue;
			if (!empty($conditions['orbase'])){
				$conditions['orbase']		.= ' OR ';
				$conditions['orbasehtml']	.= ' OR ';
			}
			$conditions['orbase'] .= str_replace('{}', str_replace("'", "''", strtolower($term)), $baseCondition);
			$conditions['orbasehtml'] .= str_replace('{}',  htmlentities(strtolower($term), ENT_QUOTES, 'UTF-8'), $baseCondition);
		}
		//Queries to concat into a big "union" sql statement
		$queries = array();
		$i = 1;
		foreach ($conditions as $condition){
			$queries[] = sprintf($baseSqlStatement, $i).$condition;
			$i++;
		}
		$fullquery	= 'SELECT DISTINCT aid, uid, catid, request_uri, article_title, search_result, cre_date FROM ('.implode(' UNION ', $queries).') AS search WHERE publish=1 ORDER BY '.$baseOrder;
		$results = array();
		if ($dbResult = $this->getDb()->getTable($fullquery))
		foreach ($dbResult as $row){
			$search_result	= html_entity_decode($row['search_result'], ENT_QUOTES, 'UTF-8');
			$firstSpaceIndex= strpos($search_result, ' ');
			$lastSpaceIndex	= strrpos($search_result, ' ');
			$termIndex		= stripos($search_result, $term);
			if ($firstSpaceIndex!==false && $firstSpaceIndex < $termIndex)
				$search_result = substr($search_result, $firstSpaceIndex);
			if ($lastSpaceIndex!==false && $lastSpaceIndex > $termIndex)
				$search_result = substr($search_result, 0, $lastSpaceIndex);
			$row['search_result'] = $search_result;
			if (!isset($results[$row['aid']]))
				$results[$row['aid']] = $row;
			else
				$results[$row['aid']]['search_result'] .= ' (...) '.$row['search_result'];
		}
		$this->_view->terms			= $baseSearch;
		$this->_view->search_results= $results;
	}
	
}