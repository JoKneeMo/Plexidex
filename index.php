<?php 
/** 
 ***Original***
 * @author Edgar Treml 
 * @copyright 2015
 ***Modifications*** 
 * @author Johnny Moore 
 * @copyright 2016 
 * start wirh parameter refresh to refresh content 
 * otherwise previously generated content will be shown (much faster) 
 * eg. www.xyz.com/plex2html/index.php?refresh 
 */ 
header('Content-type: text/html; charset=utf-8');  
include 'settings.inc'; 


$SortByOptions=array( 
    'all'=>'Alphabet', 
    'newest'=>'Release date', 
    'recentlyAdded'=>'Date added' 
); 

/** determine sort order **/ 
if (isset($_POST['sortby'])) { 
    $SortBy=$_POST['sortby']; 
} 
else {$SortBy='recentlyAdded';} 

/** show a previously scanned version **/ 
if (!isset($_GET['refresh'])) { 
    if (file_exists('./temp/list_'.$SortBy.'.htm')) { 
        $output=file_get_contents('./temp/list_'.$SortBy.'.htm'); 
        echo $output; 
        exit; 
    } 
} 

/** echo body **/ 
$HtmlBody=file_get_contents('body.htm'); 

$HtmlPart=substr($HtmlBody,0,strpos($HtmlBody,'%table%')); 
$HtmlPart=str_replace('%action%',basename(__file__).'?'.$_SERVER['QUERY_STRING'],$HtmlPart); 

$HtmlSelected=''; 
foreach ($SortByOptions as $var=>$name) { 
    $HtmlSelected.='    <option value="'.$var.'"'; 
    if ($SortBy==$var) { 
        $HtmlSelected.= ' selected'; 
    } 
     
    $HtmlSelected.='>'.$name.'</option>'."\r\n"; 
} 

$HtmlPart=str_replace('%select%',$HtmlSelected,$HtmlPart); 
$HtmlOutput=$HtmlPart; 


/** Save chosen sections - so we need write permissions **/ 
if (isset($_POST['button']) && ($_POST['button'] == 'save')) { 
    $output = ''; 
    foreach ($_POST as $name=>$key) { 
        if ($name != 'button') { 
            $output .= urldecode($name) . "\r\n"; 
        } 
    } 
    file_put_contents('./temp/sections.txt', $output); 
} 

/** Show sections to choose **/ 
if (!is_file('./temp/sections.txt')) { 
    echo 'Which sections do you want to export?<br />' . "\r\n"; 
    echo '<form id="form1" name="form1" method="post" action="' . basename(__file__) . '">' . "\r\n"; 
     
    $PlexSections = simplexml_load_file('http://' . $PlexServerIP . ':' . $PlexServerPort . 
        '/library/sections?X-Plex-Token=' . $PlexServerToken); 

    foreach ($PlexSections->Directory as $section) { 
        $SectionAttributes = $section->attributes(); 

        if ($SectionAttributes['type'] == 'movie') { 
            //echo $SectionAttributes['title'] .'/'.$SectionAttributes['key'].'<br />'; 
            echo '<input type="checkbox" name="' . urlencode($SectionAttributes['title'] . ';' . $SectionAttributes['key']) . '" />' .
                $SectionAttributes['title'] . '<br />' . "\r\n"; 
        } 
    } 
    echo '<input type="submit" name="button" id="button" value="save" />' . "\r\n"; 
    echo '</form>' . "\r\n"; 
} else { 
    /** Main program **/ 
    $file = file('./temp/sections.txt'); 
     
     
    /** make a small menue **/ 
    $sections=''; 
    for ($i=0;$i<count($file);$i++) { 
        $data=explode(';',trim($file[$i])); 
        if ($data[0]!='') { 
            $sections.='<a href="#'.$data[0].'">'.$data[0].'</a>'; 
            if (isset($file[$i+1]) && (trim($file[$i+1])!='')) { 
                $sections.=' | '; 
            } 
        } 
    } 
    $HtmlOutput=str_replace('%SectionsMenue%',$sections,$HtmlOutput); 
	
    $HtmlTable=file_get_contents('table.htm'); 

    foreach ($file as $line) { 
        $line = trim($line); 
         
        if ($line!='') { 
            $data = explode(';', trim($line)); 

            $HtmlOutput.='<h1 id="'.$data[0].'">'.$data[0].'</h1>'."\r\n";	

            $PlexMovies = simplexml_load_file('http://' . $PlexServerIP . ':' . $PlexServerPort . '/library/sections/' . $data[1] .
                '/' . $SortBy . '?X-Plex-Token=' . $PlexServerToken); 

            foreach ($PlexMovies->Video as $movie) { 
                $CurMovieTable=$HtmlTable; 

                $MovieGenres=array();
				$MovieDirectors=array();
				$MovieWriters=array();
				$MovieActors=array();
                $AudioLanguages=array(); 
                $SubLanguages=array(); 

                $MovieAttrributes = $movie->attributes(); 
				
                foreach ($movie->Genre as $genre) { 
                    $genre=iterator_to_array($genre->attributes()); 
                    $MovieGenres[]=(string) $genre['tag']; 
                }
				
				foreach ($movie->Director as $director) { 
                    $director=iterator_to_array($director->attributes()); 
                    $MovieDirectors[]=(string) $director['tag']; 
                }

				foreach ($movie->Writer as $writer) { 
                    $writer=iterator_to_array($writer->attributes()); 
                    $MovieWriters[]=(string) $writer['tag']; 
                }
				
				foreach ($movie->Role as $actor) { 
                    $actor=iterator_to_array($actor->attributes()); 
                    $MovieActors[]=(string) $actor['tag']; 
                }
                                               
                $rartingKey=$MovieAttrributes['ratingKey'];                 
                 
                /** if we want also language information and video resolution, we have to make a second request **/
                 
                $PlexMovieDetails =  simplexml_load_file('http://' . $PlexServerIP . ':' . $PlexServerPort . '/library/metadata/' . $rartingKey .
                '?X-Plex-Token=' . $PlexServerToken); 
                               
                $PlexMovieDetailsVideo=$PlexMovieDetails->Video; 
                 
                $PlexMovieDetailsMedia=$PlexMovieDetailsVideo->Media; 
				
				
                 
                foreach ($PlexMovieDetailsMedia->Part as $VideoPart) { 
                    foreach ($VideoPart->Stream as $VideoStream) { 
                        if ($VideoStream['streamType']==2) { 
                            $AudioLanguages[]=(string) $VideoStream['language']; 
                        } 
                        if ($VideoStream['streamType']==3) { 
                            $SubLanguages[]=(string) $VideoStream['language'] . ' / ' . (string) $VideoStream['title']; 
                        } 
                     }                       
                } 
                 
				$CurMovieTable=str_replace('%ThumbUrl%','?url='.$MovieAttrributes['thumb'],$CurMovieTable);
                $CurMovieTable=str_replace('%title%',$MovieAttrributes['title'],$CurMovieTable); 
                $CurMovieTable=str_replace('%year%',$MovieAttrributes['year'],$CurMovieTable); 
                $CurMovieTable=str_replace('%summary%',$MovieAttrributes['summary'],$CurMovieTable); 
                $CurMovieTable=str_replace('%releaseDate%',$MovieAttrributes['originallyAvailableAt'],$CurMovieTable);
                $CurMovieTable=str_replace('%rating%',$MovieAttrributes['contentRating'],$CurMovieTable);
				$CurMovieTable=str_replace('%duration%',$MovieAttrributes['duration'],$CurMovieTable);
				$CurMovieTable=str_replace('%tagline%',$MovieAttrributes['tagline'],$CurMovieTable);
				$CurMovieTable=str_replace('%resolution%',$PlexMovieDetailsMedia['videoResolution'],$CurMovieTable);
				$CurMovieTable=str_replace('%aCodec%',$PlexMovieDetailsMedia['audioCodec'],$CurMovieTable);
				$CurMovieTable=str_replace('%studio%',$MovieAttrributes['studio'],$CurMovieTable); 				
				$CurMovieTable=str_replace('%size%',$VideoPart['size'],$CurMovieTable); 
				$CurMovieTable=str_replace('%bitRate%',$PlexMovieDetailsMedia['bitrate'],$CurMovieTable);
				$CurMovieTable=str_replace('%vCodec%',$PlexMovieDetailsMedia['videoCodec'],$CurMovieTable);
				$CurMovieTable=str_replace('%frameRate%',$PlexMovieDetailsMedia['videoFrameRate'],$CurMovieTable);
				$CurMovieTable=str_replace('%aspect%',$PlexMovieDetailsMedia['aspectRatio'],$CurMovieTable);
				$CurMovieTable=str_replace('%channels%',$PlexMovieDetailsMedia['audioChannels'],$CurMovieTable);
				$CurMovieTable=str_replace('%VidID%',$PlexMovieDetailsMedia['id'],$CurMovieTable);
				$CurMovieTable=str_replace('%container%',$PlexMovieDetailsMedia['container'],$CurMovieTable);
				$CurMovieTable=str_replace('%streamKey%',$VideoPart['key'],$CurMovieTable);
				$CurMovieTable=str_replace('%PlexIP%',$PlexServerIP,$CurMovieTable);
				$CurMovieTable=str_replace('%PlexPort%',$PlexServerPort,$CurMovieTable);
				
				
                $CurMovieTable=str_replace('%AudioLanguages%',implode(', ',$AudioLanguages),$CurMovieTable);
                $CurMovieTable=str_replace('%SubLanguages%',implode(', ',$SubLanguages),$CurMovieTable); 
                $CurMovieTable=str_replace('%genres%',implode(', ',$MovieGenres),$CurMovieTable);
				$CurMovieTable=str_replace('%directors%',implode(', ',$MovieDirectors),$CurMovieTable);
				$CurMovieTable=str_replace('%writers%',implode(', ',$MovieWriters),$CurMovieTable);
				$CurMovieTable=str_replace('%actors%',implode(', ',$MovieActors),$CurMovieTable); 
                $HtmlOutput.=$CurMovieTable; 
            } 
        } 
    } 
} 

$HtmlPart=substr($HtmlBody,strpos($HtmlBody,'%table%')+7); 
$HtmlOutput.=$HtmlPart; 
echo $HtmlOutput; 

/** store this for later use **/ 
$HtmlOutput=str_replace('?refresh','',$HtmlOutput); 
file_put_contents('./temp/list_'.$SortBy.'.htm',$HtmlOutput); 
?>
