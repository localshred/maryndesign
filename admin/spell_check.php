<?php
session_start();
//$charset = "windows-1251";
$charset = "ISO-8859-1";
$mime = "text/html";
header("Content-Type: $mime;charset=$charset");
header('Cache-Control: no-cache');
header('Pragma: no-cache');

/////////////////////////////////////////////
//					   //
// Ver.1.0.4				   //	
// http://www.1001line.net/spell-check/    //
// Naka Gadjov	naka (at) 1001line dot net //
//					   //
/////////////////////////////////////////////

function convert_to_js($str)
{

//stringa kojto shte se predawa na JS triabwa da ima wytre \ pred wseki slujeben znak 
// ' -> \' 
// " -> \"
// \ -> \\
// cr -> \cr i t.n.
// dokato w samia php string niama nujda ot escape: toj si e surow

//!!! edna edinstwena cherta \ w reg.exp. tuk w php se predstawia kato 4broia \\\\ 
//!!! zashtoto 2broia \\ sa za php, za da gi widi edna \ , i sa nebhodimi 2 \\ za reg.exp. 
//za da izglejdat kato edna \ ot gledna tochka na reg.exp engina

//I spent some time fighting with this, so hopefully this will help someone else.
//Escaping a backslash (\) really involves not two, not three, but four backslashes to work properly.
//So to match a single backslash, one should use:
//preg_replace('/(\\\\)/', ...);

//Suppose you want to match '\n' (that's backslash-n, not newline). 
//The pattern you want is not /\\n/ but /\\\\n/. 
//The reason for this is that before the regex engine can interpret 
//the \\ into \, PHP interprets it. Thus, if you write the first, the regex engine sees \n, 
//which is reads as newline. Thus, you have to escape your backslashes twice: 
//once for PHP, and once for the regex engine.
$str=preg_replace("/('|\"|\\\\|\r)/", "\\\\\$1", $str);

//Windows CR LF
//Linux   LF
//Mac     CR
//The W3C has reccommended in the HTML4.0 specification that all browsers 
//normalize TEXTAREA (and I suppose TEXT input content) to CRLF format).

$str=preg_replace("/(\n)/","\\u000D\\u000A",$str);

#'<' and '>'
$str=preg_replace("/(<)/","\\u003C",$str);
$str=preg_replace("/(>)/","\\u003E",$str);

//$str="line1\\\r\\\nLine2"; //towa ne raboti w firefox i IE
//$str="line1\\u000D\\u000ALine2"; //towa raboti
return $str;
}

function array_sort($array, $key)
{
//sorting multi dimensional array   
   for ($i = 0; $i < sizeof($array); $i++) 
   	{
        $sort_values[$i] = $array[$i][$key];
   	}
   asort ($sort_values, SORT_NUMERIC);
   reset ($sort_values);
   while (list ($arr_key, $arr_val) = each ($sort_values)) 
   	{
        $sorted_arr[] = $array[$arr_key];
   	}
   return $sorted_arr;
}


function parseHoles ( $text )
{
global 	$DO_NOT_SPELL_CHECK_HTML, $DO_NOT_SPELL_CHECK_SCRIPT,
	$DO_NOT_SPELL_CHECK_BBCODE,
	$DO_NOT_SPELL_CHECK_EMOTICONS;

unset ($black_holes); $black_holes=array(0 => array());
unset ($pattern);
$i=0;

//define HTML match, the actual action is perform by parseWords()
//wij example za html split w dir remark_split_html/example
if ($DO_NOT_SPELL_CHECK_HTML)
    $pattern[]="/<[\/]?[\w]+[\s]?(.*?)>/is"; 

if ($DO_NOT_SPELL_CHECK_SCRIPT)
    $pattern[] = "|<script(.*?)>(.*?)</script>|is";     
    
//define BBCODE match, the actual action is perform by parseWords()	
if ($DO_NOT_SPELL_CHECK_BBCODE)
{ 
    //match all single chars tags - start and closing [b][i][u][/b][/i][/u]
    //this match is nonsense, because it is single char match, which is avoided by spellcheck()
    $pattern[] = "|\[/?[biu]\]|is"; 	
    
    $pattern[] = "|\[/?quote\]|is"; //quote match is also nonsense, because it is valid english word, but not valid in others languages
    
    //avoid spell check between [code]--[/code] including [code]-tags
    $pattern[] = "|\[code\](.*?)\[/code\]|is"; 
    $pattern[] = "|\[/?code\]|is";	 // if the [code] or [/code] is alone
    
    $pattern[] = "|\[list(=[1a])?\]|is"; //list starting tag
    $pattern[] = "|\[/list\]|is";	 //list closing tag
    $pattern[] = "|\[\*\]|is";		 //list item tag
    
    $pattern[] = "|\[img\](.*?)\[/img\]|is";
    $pattern[] = "|\[/?img\]|is";	 // if it is alone
    
    $pattern[] = "|\[url\](.*?)\[/url\]|is"; //url style 1, do not spell check between [url]..[/url]
    $pattern[] = "|\[url=http://.*?\]|is"; //url style 2, do not spell check [url=http://...]
    $pattern[] = "|\[/?url\]|is"; 	   //url style 2, end tag, do  not spell check [url] [/url]
    
    //(.+?)\] towa ne raboti prawilno pri ='' zashtoto iska obezatelno 1 simwol (+ 1..n) a toj e nakraia
    // i go wzima ot krajnata skoba ] i niama s kakwo da se zatwori, t.e prodyljawa go do sledwashtata ]
    // |\[color=(.+?)\]|is ne moje da opredeli prawilno pyrwia tag ako e =''
    // i wrezult matcha na [color=]cherveno[/color] sht byde "[color=]cherveno[/color]"
    // obache |\[color=(.*?)\]|is raboti prawilno pri =''
    $pattern[] = "|\[color=[\S]+?\]|is";
    $pattern[] = "|\[/color\]|is";
    
    $pattern[] = "|\[size=[0-9]+?\]|is";
    //$pattern[] = "|\[color=(.*?)\]|is";
    //$pattern[] = "|\[size=(.*?)\]|is";
    $pattern[] = "|\[/size\]|is";
    
} 



//!!! PREG_OFFSET_CAPTURE doesn't work with PREG_SET_ORDER
//!!! PREG_OFFSET_CAPTURE flag is available since PHP 4.3.0
foreach ($pattern as $value) 
{ 
	unset ($matches);
	preg_match_all($value, $text, $matches, PREG_SET_ORDER);
/*
print("matches\n");
print("<PRE>\n");
printf("%s", format_html_code( print_r($matches,true) ) );
print("</PRE>\n");
print("####\n\n");
*/
//obtain OFFSET of the main match
	$offset=0; 
	foreach ($matches as $value) 
		{
		//$value[0] is the whole match, $value[1] is the next sub match
		$start_pos= strpos($_SESSION["first_time_text"], $value[0], $offset);
		$offset = $start_pos + strlen($value[0]);
		$black_holes[$i][0]=$start_pos;  //start position of black hole
		$black_holes[$i][1]=($offset-1); //end position of black hole
		$black_holes[$i]['match']=$value[0]; //for debuging only not used in script
		$i++;
		}
	
}

/*
//test include empty hole at pos 11
//with start-position-hole=end-position-hole
$black_holes[$i][0]=11;  //start position of black hole
$black_holes[$i][1]=11; //end position of black hole
$black_holes[$i]['match']='';
$i++;
*/

//uncoment to test unsorted black_holes
/*
print("black_holes_unsorted\n");
print("<PRE>\n");
printf("%s", format_html_code( print_r($black_holes,true) ) );
print("</PRE>\n");
print("####<BR>\n\n");
*/

//sort the black holes, some early start-end positions may appear after old start-end positions
//sort the by key 0 : $black_holes[][0]
$black_holes=array_sort($black_holes, 0);


//uncoment to test sorted black_holes
/*
print("black_holes_sorted\n");
print("<PRE>\n");
printf("%s", format_html_code( print_r($black_holes,true) ) );
print("</PRE>\n");
print("####<BR>\n\n");
*/

return $black_holes;
}

function parseWords($text, $black_holes, &$words, &$words_start_pos)
{
$i=0; $offset=0;
$start_pos=0;

foreach ($black_holes as $hole)
{
//printf("black-hole-loop<BR>\n");
$end_pos=$hole[0]-1; //start of the current hole, excluding start char (-1)	
		   //start_pos here is from previous hole (loop)	
	if ($start_pos <= $end_pos)	//process normally no nested black holes
	{
		$part = substr( $text, $start_pos, ($end_pos - $start_pos + 1) );
		
		//uncoment to test
		//printf("<code> start:stop  |string|  %s:%s   |%s|</code><BR>\n",$start_pos,$end_pos,$part);	
		
		//split the part of text outside of the black holes into words
		//za >= PHP 4.3.0 ima opcia PHP PREG_SPLIT_OFFSET_CAPTURE
		//!!! towa dawa greshka na bulgarian 'ч' цепи на две думите със 'ч' !!! wij serach.php	
		//!!! '/[^a-zA-Zа-яА-Я0-9_]+?/'		
		$words_into_part = preg_split('/[\W]+?/',$part, -1, PREG_SPLIT_NO_EMPTY);
		$offset = $start_pos;
		foreach ($words_into_part as $value) 
			{
			$words_start_pos[$i] = strpos($text, $value, $offset);
			$words[$i]=$value;
			$offset = $words_start_pos[$i]+ strlen($value);
			$i++;
			}
	}

if ($hole[0]==$hole[1]) 
	$new_start_pos=$hole[1]; //some rare case -empty hole: start-position-hole=end-position-hole
				 //or empty element in array $black_holes
				 //this also appear when there in no $black_holes at all - first element is empty
else 	$new_start_pos=$hole[1]+1; //end of the current hole, excluding end char (+1)

//there is possibility that current hole is completely inside of the previuos hole
//if the end of the current ($hole[1]) is < than the end of the previuos: do nothing, keep the old $start_pos 
if ( $new_start_pos > $start_pos ) $start_pos = $new_start_pos; 

		
}		

//process from the last hole to the end of the string, or process if there is no holes.
$end_pos=strlen($text)-1;
		$part = substr( $text, $start_pos, ($end_pos - $start_pos + 1) );
		//uncoment to test
		//printf("<code> start:stop  |lastpart string|  %s:%s   |%s|</code><BR>\n",$start_pos,$end_pos,$part);	
		//split the part of text outside of the black holes into words
		//za >= PHP 4.3.0 ima opcia PHP PREG_SPLIT_OFFSET_CAPTURE
		//!!! towa dawa greshka na bulgarian 'ч' цепи на две думите със 'ч' !!! wij serach.php	
		//!!! '/[^a-zA-Zа-яА-Я0-9_]+?/'			
		$words_into_part = preg_split('/[\W]+?/',$part, -1, PREG_SPLIT_NO_EMPTY);
		$offset = $start_pos;
		foreach ($words_into_part as $value) 
			{
			$words_start_pos[$i] = strpos($text, $value, $offset);
			$words[$i]=$value;
			$offset = $words_start_pos[$i]+ strlen($value);
			$i++;
			}		

}
function spellcheck ( $words ) 
{
   //wryshta masiw w kojto sa:
   //nomera (ot 0-la) na greshnata duma: $misspelled['word_no']
   //masiw sys predlojeniata za korekcia na greshanta duma: $misspelled['suggest']
   $int = pspell_new('en');
   $word_no=0; $i=0;
   foreach ($words as $value) 
   	{
       	//if the word is numeric , pspell incorectlly say it is misspelled 
	//if (!ctype_digit ($value)) //w RH7.3 ne e definirana
	if (!is_numeric($value))
	if (!pspell_check($int, $value)) 
           	{
		$misspelled[$i]['word_no'] = $word_no;
   	   	$misspelled[$i]['suggest'] = pspell_suggest($int, $value);
		$i++;
		}
	$word_no++;
	}
 
   return $misspelled;
}
function correct_word($miss_word_counter,&$correct_pos,$suggest)
{
//tazi funkcia deistwa naprawo wyrhu $_SESSION["temp_corrected"] i ia widoizmenia
//oswen towa i koregira i wrishta greshkata $correct_pos po address

//poluchawane nomera na dumata koiato triabwa da se koregira
$num_word = $_SESSION['misspelled'][$miss_word_counter]['word_no'];

//poluchawane statowata pozicia na greshnata duma
$start_pos=$_SESSION["words_start_pos"][$num_word]+$correct_pos;

//poluchawane dyljinata na sreshnata duma
$L=strlen($_SESSION["words"][$num_word]);

//syshtinskata korekcia 
$T=$_SESSION["temp_corrected"];
//print ("suggest: $suggest <BR>\n");
//print ("start_pos: $start_pos <BR>\n");
//print ("L: $L <BR>\n");

$_SESSION["temp_corrected"] = substr_replace( $T, $suggest, $start_pos, $L);
//printf ("temp string corrected: %s <BR>\n", $_SESSION['temp_corrected']);


//koregirane na greshkata pri korekciata
$correct_pos = $correct_pos + (strlen($suggest)-$L);
//print ("correct_pos: $correct_pos <BR>\n");
}

function format_html_code($str)
{
$str = htmlspecialchars($str, ENT_QUOTES);
//write here any additional formatting and exceptions

//keep white space formatting
//to keep visual space formating convert every second space to  &nbsp;

$str = str_replace ("  ", " &nbsp;", $str);
// 1 space: ' ' 	-> ' '
// 2 spaces '  '	-> ' &nbsp;'
// 3 spaces '   '	-> ' &nbsp; '
// 4 spaces '    '	-> ' &nbsp; &nbsp;'

//if we replace every one space by &nbsp; the brouser do not wrap lines inside <DIV> tag
//this may be browser bug: 
//example: xxxx&nbsp;yyyyy&nbsp;&nbsp;zzzzz&nbsp;&nbsp;&nbsp;aaaa
//this line have spaces but it is consider by 1 long line and do not wrap

//not working
//$str = str_replace ("\t", "&#09;", $str);

//to do
//   $message    = str_replace ("[U]", "<U>", "$message");
//   $message    = str_replace ("[/U]", "</U>", "$message");
//   $message    = str_replace ("[I]", "<I>", "$message");
//   $message    = str_replace ("[/I]", "</I>", "$message");
//   $message    = str_replace ("[B]", "<B>", "$message");
//   $message    = str_replace ("[/B]", "</B>", "$message");

return $str;
}

function format_output($str)
{
//parts of text to be special Interpretated during preview
/*
    //replacment patterns - !!ne sa za tuk
    //$pattern[] = "|\[p\]|s";
    $pattern[] = "|\[b\](.*?)\[/b\]|s";
    $pattern[] = "|\[i\](.*?)\[/i\]|s";
    $pattern[] = "|\[u\](.*?)\[/u\]|s";
    $pattern[] = "|\[center\](.*?)\[/center\]|s";
    $pattern[] = "|\[url\](.*?)\[/url\]|s";
    $pattern[] = "|\[url=(.*?)\](.*?)\[/url\]|s";
    $pattern[] = "|\[hr\](.*?),(.*?)\[/hr\]|s";
    $pattern[] = "|\[img\](http://.*?)\[/img\]|s";
    $pattern[] = "|\[img\]([0-9]+)(\.[a-zA-Z0-9]{0,10})\[/img\]|s";
    */
}

function display_red($miss_word_counter,$correct_pos)
{
//$err_color =	'red'; 
//$err_color =	'HotPink'; 
//$err_color =	'DeepPink';
$err_color =	'#fe7173';
$err_color2 =	'#fea1c0'; 
$start_str =	"<span style=\"background:$err_color2;\">";
$start_str_bold ="<span style=\"background:$err_color; font-weight: bold;\">";
$end_str   =  	"</span>";

//$_SESSION["temp_corrected"];

$current_pos=0; //poziciata w stringa po simwoli, an ne poziciqta na dumite
$screen_text='';

$num_elements=sizeof($_SESSION['misspelled']);

for ( $i=$miss_word_counter; $i<$num_elements; $i++)
	{
	$num_word = $_SESSION['misspelled'][$i]['word_no'];
	//print ("$num_word <BR>\n");
	
	//poluchawane statowata pozicia na greshnata duma
	$start_pos=$_SESSION["words_start_pos"][$num_word]+$correct_pos;
	//poluchawane kraynata pozicia na greshnata duma
	$end_pos=$_SESSION["words_start_pos"][$num_word]+strlen($_SESSION["words"][$num_word])+$correct_pos;
	
	$piece_before=substr($_SESSION["temp_corrected"], $current_pos, $start_pos - $current_pos);
	$piece_middle=substr($_SESSION["temp_corrected"], $start_pos, strlen($_SESSION["words"][$num_word]) );
	
	$piece_before=format_html_code($piece_before);
	$piece_middle=format_html_code($piece_middle);
	//printf("<BR>");
	//printf("piece_before: %s<BR>\n",$piece_before);
	//printf("piece_middle: %s<BR>\n",$piece_middle);
	
	if ($i==$miss_word_counter)
	$screen_text=$screen_text.$piece_before.$start_str_bold.$piece_middle.$end_str;
	else
	$screen_text=$screen_text.$piece_before.$start_str.$piece_middle.$end_str;
	
	$current_pos=$end_pos;
	//$i++;
	}
//return all from end of the last spell word, to the end of the whole string
$screen_text=$screen_text.format_html_code( substr($_SESSION["temp_corrected"], $current_pos) );

//$screen_text=preg_replace("/\n/","<br>\n",$screen_text);
//towa kato che li e po prawilno na win system
$screen_text=preg_replace("/(\r\n|\n|\r)/","<br>\n",$screen_text);

print("$screen_text");
}

function display_nav($miss_word_counter)
{
print ("<table width=\"100%\" border=0 cellspacing=0 cellpadding=5>\n");
print("<TR>\n");

print("<TD width=\"10%\" align=\"left\" valign=\"top\">\n");
//moje bi triabwa da ima algoritym za opredeliane na the best match ot wsichki dumi koito aspell predlaga
$the_best_match=$NULL;
printf("<FORM name=\"sform\" method=\"GET\" action=\"%s\">\n",$_SERVER['PHP_SELF']);

printf("
			<SELECT name=\"asuggest\" SIZE=10>\n
			<optgroup label=\"Select The Best Match\">\n
			<OPTION selected=\"true\">%s\n",$the_best_match);
			//printf("<OPTION>%s\n",$NULL); // i syshto da moje da se zadawa bez default suggest
			while (list( ,$value) = @each ($_SESSION['misspelled'][$miss_word_counter]['suggest']))
			{
			printf("<OPTION>%s\n",$value);
			}
			printf("</optgroup>\n");
			printf("</SELECT>\n");
print("<BR>\n");
printf("<INPUT type=\"text\" name=\"csuggest\" value=\"%s\" size=\"16\" maxlength=\"24\">\n", '');
print("</TD>\n");

print("<TD align=\"left\" valign=\"top\">\n");
printf("<INPUT type=\"hidden\" name=\"miss_word_counter\" value=\"%s\">\n", $miss_word_counter);
print("<INPUT TYPE=\"submit\" value=\"Correct\">");
print("</FORM>\n");
print(" &nbsp&nbsp ");
printf("<a href=\"%s?miss_word_counter=%s&next=yes\">Skip / Next ></a>\n",$_SERVER['PHP_SELF'],$miss_word_counter);
printf("<P>\n");

//stringa kojto shte se predawa na JS triabwa da ima wytre \ pred wseki slujeben znak 
// ' -> \' 
// " -> \"
// \ -> \\
// cr -> \cr i t.n.
// dokato w samia php string niama nujda ot escape: toj si e surow

$wb_str=convert_to_js($_SESSION["temp_corrected"]);
//$wb_str='lssd \\\"gggg';

printf("<script language=\"JavaScript\">\n");
printf("<!--\n");
printf("var wb_str= \"%s\";\n",$wb_str);
printf("-->\n");
printf("</script>\n");

//izglejda che ne moje da se predade naprawo stringa na po-dolnata funkcia
//a triabwa pyrwo prez promenliwa (wij po gore)
//problema e sys " :naprimer towa ne raboti wypreki che " e escapnat!
//WriteBack('form','field', 'xxx \" yyy');
printf("<a href=\"javascript: void(0);\" onclick=\"WriteBack('%s','%s', wb_str);\">Apply / Finish</A>",$_SESSION["form_name"], $_SESSION["field_name"]);
printf("<P><BR><BR>\n");
printf("<a href=\"\" onclick=\"window.close();\">Close</A>");
printf("<P>\n");
print("</TD>\n");

print("<TD align=\"left\" valign=\"bottom\">\n");
print ("<div style=\"font-size: smaller; font-weight: normal; position: relative; bottom: -10px; right: -5px; z-index: 1; color: gray; text-align: right;\">");
printf("Design by: <a href=\"http://www.1001Line.com/\" target=\"_blank\" style=\"text-decoration: none; color: gray;\">www.1001Line.com</a> Ver.1.0.4\n");
print ("</div>");
print("</TD>\n");

print("</TR>\n");
print("</TABLE>\n");

}



?>
<html>
<head>
	<title>PHP Spell Check</title>
	<script type="text/javascript">
	function WriteBack(form_name,field_name,corrected_text) {
	  //alert (corrected_text);
	  opener.document[form_name][field_name].value = corrected_text;
	}
	</script>
</head>

<body>
<?php
// Customize script
// default is disable: custom_var= '' or (unset) or "// - comment var" or  =FALSE
// to set var give it value for example: custom_var='YES' !!!BUT NOT custom_var='NO'

$DO_NOT_SPELL_CHECK_HTML = 'YES';		//	FALSE; //exclude html tags from spell checking
$DO_NOT_SPELL_CHECK_SCRIPT = 'YES';	//	FALSE; //exclude all between <script></script> tags , including tags

//it is always save to be ='YES' here 
//example: <img alt="the dog" src="dog.gif"> become-> &lt;img&nbsp;alt=&quot;the&nbsp;dog&quot;&nbsp;src=&quot;dog.gif&quot;&gt;
//otherwise (=FALSE) display html tags as they are - like in browser; useful for some "Editize" JAVA textarea replacement: www.editize.com
$INTERPRET_HTML_CODE = 'YES'; 		

//all between <script>xxx</script> tags , including tags become-> &lt;img&nbsp;alt=&quot;the&nbsp;dog&quot;&nbsp;src=&quot;dog.gif&quot;&gt;
//(=FALSE) results are inpredictible, for testing purpose only. display <script> as they are - like in browser
$INTERPRET_SCRIPT_CODE = 'YES';

$REST_OF_TEXT_SPECIALCHARS = 'YES';       // ' '->&nbsp; '<' -> &lt; '"'-> &quot; and so on...

//phpbb.com stuff http://www.phpbb.com/phpBB/faq.php?mode=bbcode
$DO_NOT_SPELL_CHECK_BBCODE = 'YES';
$DO_NOT_SPELL_CHECK_EMOTICONS = 'YES';
$PREVIEW_BBCODE = 'YES';
$PREVIEW_EMOTICONS = 'YES';


//work regardles of register_globals
$init = 		$_GET['init'];
$miss_word_counter = 	$_GET['miss_word_counter'];
$next = 		$_GET['next'];
$asuggest =		$_GET['asuggest'];
$csuggest =		$_GET['csuggest'];

if ($init=='nojs') {
	//perform init if JavaScript is disabled in brouser
	$_SESSION["form_name"]=$_POST['form_name']; //this var is not needed, but assigned to keep good style ...
	$_SESSION["field_name"]=$_POST['field_name']; //this var is not needed, but assigned to keep good style ...
	$_SESSION["first_time_text"] = "<div style=\"text-align: center;\"><b>This is Simple PHP Spell Checker.</b> It is used to Spell Check user input forms, text area and input fields.</div>\n
	<B>IF YOU SEE THIS TEXT</B>, you probably have <B>JavaScript turned off</B>. Click browser's 'BACK' button to return to the form and enable JavaScript in your Browser.\n
	After successfully done, this window must Popup and contains the user input text she wants to spellcheck.
	";
	unset ($_SESSION["words"]);
	unset ($_SESSION["words_start_pos"]);
	unset ($_SESSION["misspelled"]);
	$_SESSION["temp_corrected"] = $_SESSION["first_time_text"];
	$_SESSION["correct_pos"]=0;
}
elseif ($init=='yes')	{
	//perform init and SpellChecking via spellcheck()
	//the rest of the script, outside of this statement is just an interface 
	//print("Initialization.<BR>\n\n");
	$_SESSION["form_name"]=$_POST['form_name'];
	$_SESSION["field_name"]=$_POST['field_name'];
	$_SESSION["first_time_text"]=stripslashes($_POST['first_time_text']);
	
	//NORMALIZE NEW LINES
	// Convert PC newline (CRLF)
	// to Unix newline format (LF)
	$_SESSION["first_time_text"]=preg_replace("/(\r\n)/","\n",$_SESSION["first_time_text"]);
	//Convert Mac newline (CR)
	//to Unix newline format (LF)
	$_SESSION["first_time_text"]=preg_replace("/(\r)/","\n",$_SESSION["first_time_text"]);

	//define $black_holes array: parts of text to be excluded from spell checking
	$black_holes=parseHoles($_SESSION["first_time_text"]);
	/*
	$words = preg_split('/[\W]+?/',$_SESSION["first_time_text"], -1, PREG_SPLIT_NO_EMPTY);
	//za >= PHP 4.3.0 ima opcia PHP PREG_SPLIT_OFFSET_CAPTURE
	//i po dolnia cikal e nenujen no pyk poziciite shte se wyrnat w syshtia masiw kato dumite
	$i=0; $offset=0; unset ($words_start_pos);
	foreach ($words as $value) 
		{
		$words_start_pos[$i] = strpos($_SESSION["first_time_text"], $value, $offset);
		$offset = $words_start_pos[$i]+ strlen($value);
		$i++;
		}
	
	*/
	unset ($words_start_pos);
	$words = array();
	$words_start_pos = array();
	
	//return by var arrays $words, $words_start_pos :take respect of $black_holes
	parseWords($_SESSION["first_time_text"], $black_holes, $words, $words_start_pos);
	
	$_SESSION["words"] = $words;
	$_SESSION["words_start_pos"] = $words_start_pos;
	$_SESSION["misspelled"] = spellcheck($words);
	$_SESSION["temp_corrected"] = $_SESSION["first_time_text"];
	$_SESSION["correct_pos"]=0;
}

//Some debug. Uncoment to test

/*
print("words\n");
print("<PRE>\n");
print_r($_SESSION["words"]);
print("</PRE>\n");
print("####\n\n");


print("starting positions\n");
print("<PRE>\n");
print_r($_SESSION["words_start_pos"]);
print("</PRE>\n");
print("####\n\n");


print("misspelled\n");
print("<PRE>\n");
print_r($_SESSION["misspelled"]);
print("</PRE>\n");
print("####\n\n");
*/

//this is error position, which occure when word to replace is shorter or longer then misspeled word
//$correct_pos can be +, zero or negative
//$correct_pos is corrected it self every time we made an replacement
//$correct_pos = $correct_pos + (strlen(..... ; see correct_word()
$correct_pos=$_SESSION["correct_pos"];

if (!isset($miss_word_counter) or $miss_word_counter == '') {
	$miss_word_counter=0;
}

settype($miss_word_counter,"integer");
if (isset($_SESSION['misspelled'][$miss_word_counter]['word_no'])) {
	if ($csuggest != '') {
		correct_word($miss_word_counter, $correct_pos, $csuggest);
		$miss_word_counter++;
	}
	elseif ($asuggest != '') {
		correct_word($miss_word_counter, $correct_pos, $asuggest);
		$miss_word_counter++;
	}
	if ($next=='yes') $miss_word_counter++;
}

print("<DIV style=\"width: auto; background:#f2f2f2; padding:10pt; border-style: none; border-width: medium; margin-bottom:0px;\">\n");
//print("<code>");
display_red($miss_word_counter,$correct_pos);
//print("</code>");
print("</DIV>\n");

print ("<div style=\"margin-top:0px; color:gray; text-align:center;\">");
print ("miss word counter: $miss_word_counter <BR>\n");
print ("</div>");

print("<div style=\"width: auto; background:#eaeff4; padding:10pt; border-style: none; border-width: medium; margin-bottom:0px;\">\n");
display_nav($miss_word_counter);
print ("</div>");

//printf("The form_name to spell is: %s<BR>\n",$_SESSION["form_name"]);
//printf("The field_name in form to spell is: %s<BR>\n",$_SESSION["field_name"]);

$_SESSION["correct_pos"]=$correct_pos;

?>

</body>
</html>
