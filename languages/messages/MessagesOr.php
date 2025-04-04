<?php
/** Oriya (ଓଡ଼ିଆ)
 *
 * @file
 * @ingroup Languages
 *
 * @author Ansumang
 * @author Jayantanth
 * @author Jnanaranjan Sahu
 * @author Jose77
 * @author MKar
 * @author Odisha1
 * @author Psubhashish
 * @author Sambiwiki
 * @author Shijualex
 * @author Shisir 1945
 * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
 * @author ଶିତିକଣ୍ଠ ଦାଶ
 */

$digitTransformTable = [
	'0' => '୦', # U+0B66
	'1' => '୧', # U+0B67
	'2' => '୨', # U+0B68
	'3' => '୩', # U+0B69
	'4' => '୪', # U+0B6A
	'5' => '୫', # U+0B6B
	'6' => '୬', # U+0B6C
	'7' => '୭', # U+0B6D
	'8' => '୮', # U+0B6E
	'9' => '୯', # U+0B6F
];

$numberingSystem = 'orya';

$linkTrail = "/^([a-z\x{0B00}-\x{0B7F}]+)(.*)$/sDu";

/** namespace translations from translatewiki.net
 * @author Shijualex
 * @author Psubhashish
 */
$namespaceNames = [
	NS_MEDIA            => 'ମାଧ୍ୟମ',
	NS_SPECIAL          => 'ବିଶେଷ',
	NS_TALK             => 'ଆଲୋଚନା',
	NS_USER             => 'ବ୍ୟବହାରକାରୀ',
	NS_USER_TALK        => 'ବ୍ୟବହାରକାରୀଙ୍କ_ଆଲୋଚନା',
	NS_PROJECT_TALK     => '$1_ଆଲୋଚନା',
	NS_FILE             => 'ଫାଇଲ',
	NS_FILE_TALK        => 'ଫାଇଲ_ଆଲୋଚନା',
	NS_MEDIAWIKI        => 'ମିଡ଼ିଆଉଇକି',
	NS_MEDIAWIKI_TALK   => 'ମିଡ଼ିଆଉଇକି_ଆଲୋଚନା',
	NS_TEMPLATE         => 'ଛାଞ୍ଚ',
	NS_TEMPLATE_TALK    => 'ଛାଞ୍ଚ_ଆଲୋଚନା',
	NS_HELP             => 'ସହଯୋଗ',
	NS_HELP_TALK        => 'ସହଯୋଗ_ଆଲୋଚନା',
	NS_CATEGORY         => 'ଶ୍ରେଣୀ',
	NS_CATEGORY_TALK    => 'ଶ୍ରେଣୀ_ଆଲୋଚନା',
];

$namespaceAliases = [
	'ବ୍ୟବହାରକାରି'          => NS_USER,
	'ବ୍ୟବହାରକାରିଁକ_ଆଲୋଚନା' => NS_USER_TALK,
	'ବ୍ୟବାହାରକାରୀ'          => NS_USER,
	'ବ୍ୟବାହାରକାରୀଙ୍କ_ଆଲୋଚନା' => NS_USER_TALK,
	'ଉଇକିପିଡ଼ିଆ_ଆଲୋଚନା' => NS_PROJECT_TALK,
	'ଟେଁପଲେଟ'             => NS_TEMPLATE,
	'ଟେଁପଲେଟ_ଆଲୋଚନା'     => NS_TEMPLATE_TALK,
	'ଟେମ୍ପଲେଟ'             => NS_TEMPLATE,
	'ଟେମ୍ପଲେଟ_ଆଲୋଚନା'     => NS_TEMPLATE_TALK,
	'ବିଭାଗ'                => NS_CATEGORY,
	'ବିଭାଗିୟ_ଆଲୋଚନା'      => NS_CATEGORY_TALK,
	'ସାହାଯ୍ୟ'                => NS_HELP,
	'ସାହାଯ୍ୟ_ଆଲୋଚନା'      => NS_HELP_TALK,
];

/** @phpcs-require-sorted-array */
$specialPageAliases = [
	'Activeusers'               => [ 'ସଚଳସଭ୍ୟ' ],
	'Allmessages'               => [ 'ସବୁସନ୍ଦେଶ' ],
	'Allpages'                  => [ 'ସବୁପୃଷ୍ଠା' ],
	'Ancientpages'              => [ 'ପୁରୁଣାପୃଷ୍ଠା' ],
	'Badtitle'                  => [ 'ଖରାପନାମ' ],
	'Blankpage'                 => [ 'ଖାଲିପୃଷ୍ଠା' ],
	'Block'                     => [ 'ଅଟକାଇବେ', 'ଆଇପିଅଟକାଇବେ', 'ସଭ୍ୟଅଟକାଇବେ' ],
	'BlockList'                 => [ 'ତାଲିକାଅଟକାଇବେ' ],
	'Booksources'               => [ 'ବହିସ୍ରୋତ' ],
	'BrokenRedirects'           => [ 'ଭଙ୍ଗାଲେଉଟାଣି' ],
	'Categories'                => [ 'ଶ୍ରେଣୀ' ],
	'ChangeEmail'               => [ 'ଇମେଲବଦଳାଇବେ' ],
	'ChangePassword'            => [ 'ପାସୱାର୍ଡ଼ବଦଳାଇବେ', 'ପାସୱାର୍ଡ଼ସେଟକରିବେ' ],
	'ComparePages'              => [ 'ପୃଷ୍ଠାକୁତଉଲିବେ' ],
	'Confirmemail'              => [ 'ଇମେଲଥୟକରିବେ' ],
	'Contributions'             => [ 'ଅବଦାନ' ],
	'CreateAccount'             => [ 'ଖାତାଖୋଲିବେ' ],
	'Deadendpages'              => [ 'ଆଗକୁରାହାନଥିବାପୃଷ୍ଠା' ],
	'DeletedContributions'      => [ 'ହଟାଇଦିଆଯାଇଥିବାଅବଦାନ' ],
	'DoubleRedirects'           => [ 'ଦୁଇଥରଲେଉଟାଣି' ],
	'EditWatchlist'             => [ 'ଧ୍ୟାନସୂଚୀବଦଳାଇବେ' ],
	'Emailuser'                 => [ 'ସଭ୍ୟଙ୍କୁମେଲକରିବେ' ],
	'Export'                    => [ 'ରପ୍ତାନି' ],
	'Fewestrevisions'           => [ 'ସବୁଠୁକମସଙ୍କଳନ' ],
	'FileDuplicateSearch'       => [ 'ଫାଇଲନକଲିଖୋଜା' ],
	'Filepath'                  => [ 'ଫାଇଲରାସ୍ତା' ],
	'Import'                    => [ 'ଆମଦାନି' ],
	'Invalidateemail'           => [ 'କାମକରୁନଥିବାଇମେଲ' ],
	'JavaScriptTest'            => [ 'ଜାଭାସ୍କ୍ରିପ୍ଟଟେଷ୍ଟ' ],
	'LinkSearch'                => [ 'ଲିଙ୍କଖୋଜା' ],
	'Listadmins'                => [ 'ପରିଛାତାଲିକା' ],
	'Listbots'                  => [ 'ବଟତାଲିକା' ],
	'Listfiles'                 => [ 'ଫାଇଲତାଲିକା' ],
	'Listgrouprights'           => [ 'ଗୋଠନିୟମତାଲିକା' ],
	'Listredirects'             => [ 'ଲେଉଟାଣିତାଲିକା' ],
	'Listusers'                 => [ 'ସଭ୍ୟତାଲିକା' ],
	'Lockdb'                    => [ 'ଡାଟାବେସ‌କିଳିଦେବା' ],
	'Log'                       => [ 'ଲଗ' ],
	'Lonelypages'               => [ 'ଏକୁଟିଆପୃଷ୍ଠା' ],
	'Longpages'                 => [ 'ଲମ୍ବାପୃଷ୍ଠା' ],
	'MergeHistory'              => [ 'ଇତିହାସକୁମିଶାଇବେ' ],
	'MIMEsearch'                => [ 'MIME_ଖୋଜା' ],
	'Mostcategories'            => [ 'ଅଧିକଶ୍ରେଣୀଥିବା' ],
	'Mostimages'                => [ 'ଅଧିକଯୋଡ଼ାଫାଇଲ' ],
	'Mostlinked'                => [ 'ଅଧିକଯୋଡ଼ାପୃଷ୍ଠା' ],
	'Mostlinkedcategories'      => [ 'ଅଧିକଯୋଡ଼ାଶ୍ରେଣୀ' ],
	'Mostlinkedtemplates'       => [ 'ଅଧିକଯୋଡ଼ାଛାଞ୍ଚ' ],
	'Mostrevisions'             => [ 'ଅଧିକସଙ୍କଳନ' ],
	'Movepage'                  => [ 'ପୃଷ୍ଠାଘୁଞ୍ଚାଇବେ' ],
	'Mycontributions'           => [ 'ମୋଅବଦାନ' ],
	'Mypage'                    => [ 'ମୋପୃଷ୍ଠା' ],
	'Mytalk'                    => [ 'ମୋଆଲୋଚନା' ],
	'Myuploads'                 => [ 'ମୋଅପଲୋଡ଼' ],
	'Newimages'                 => [ 'ନୂଆଫାଇଲ' ],
	'Newpages'                  => [ 'ନୂଆପୃଷ୍ଠା' ],
	'PermanentLink'             => [ 'ଚିରକାଳଲିଙ୍କ' ],
	'Preferences'               => [ 'ପସନ୍ଦ' ],
	'Prefixindex'               => [ 'ଆଗରେଯୋଡ଼ାହେବାଇଣ୍ଡେକ୍ସ' ],
	'Protectedpages'            => [ 'କିଳାଯାଇଥିବାପୃଷ୍ଠା' ],
	'Protectedtitles'           => [ 'କିଳାଯାଇଥିବାନାମ' ],
	'Randompage'                => [ 'ଜାହିତାହି', 'ଜାହିତାହିପୃଷ୍ଠା' ],
	'Randomredirect'            => [ 'ଜାହିତାହିଲେଉଟାଣି' ],
	'Recentchanges'             => [ 'ନଗଦବଦଳ' ],
	'Recentchangeslinked'       => [ 'ଜୋଡ଼ାଥିବାନଗଦବଦଳ', 'ପାଖାପାଖିବଦଳ' ],
	'Revisiondelete'            => [ 'ସଙ୍କଳନଲିଭାଇଦିଅଦେବେ' ],
	'Search'                    => [ 'ଖୋଜନ୍ତୁ' ],
	'Shortpages'                => [ 'ଛୋଟପୃଷ୍ଠା' ],
	'Specialpages'              => [ 'ବିଶେଷପୃଷ୍ଠା' ],
	'Statistics'                => [ 'ଗଣନା' ],
	'Tags'                      => [ 'ଚିହ୍ନସମୂହ' ],
	'Unblock'                   => [ 'ଫିଟାଇଦେବେ' ],
	'Uncategorizedcategories'   => [ 'ଅସଜଡ଼ାଶ୍ରେଣୀ' ],
	'Uncategorizedimages'       => [ 'ଅସଜଡ଼ାଶ୍ରେଣୀରଫାଇଲ' ],
	'Uncategorizedpages'        => [ 'ଅସଜଡ଼ା_ଫାଇଲସବୁ' ],
	'Uncategorizedtemplates'    => [ 'ଅସଜଡ଼ାଛାଞ୍ଚ' ],
	'Undelete'                  => [ 'ଅଣଲିଭା' ],
	'Unlockdb'                  => [ 'DBଖୋଲିବା' ],
	'Unusedcategories'          => [ 'ବ୍ୟବହାରହୋଇନଥିବାଶ୍ରେଣୀ' ],
	'Unusedimages'              => [ 'ବ୍ୟବହାରହୋଇନଥିବାଫାଇଲ' ],
	'Unusedtemplates'           => [ 'ବ୍ୟବହାରହୋଇନଥିବାଛାଞ୍ଚ' ],
	'Unwatchedpages'            => [ 'ଦେଖାଯାଇନଥିବାପୃଷ୍ଠାସବୁ' ],
	'Upload'                    => [ 'ଅପଲୋଡ଼' ],
	'UploadStash'               => [ 'ଷ୍ଟାସଅପଲୋଡ଼' ],
	'Userlogin'                 => [ 'ସଭ୍ୟଲଗଇନ' ],
	'Userlogout'                => [ 'ସଭ୍ୟଲଗଆଉଟ' ],
	'Userrights'                => [ 'ସଭ୍ୟଅଧିକାର' ],
	'Version'                   => [ 'ସଂସ୍କରଣ' ],
	'Wantedcategories'          => [ 'ଦରକାରିଶ୍ରେଣୀ' ],
	'Wantedfiles'               => [ 'ଦରକାରିଫାଇଲ' ],
	'Wantedpages'               => [ 'ଦରକାରିପୃଷ୍ଠା' ],
	'Wantedtemplates'           => [ 'ଦରକାରିଛାଞ୍ଚ' ],
	'Watchlist'                 => [ 'ଦେଖଣାତାଲିକା' ],
	'Whatlinkshere'             => [ 'ଏଠାରେକଣଲିଙ୍କଅଛି' ],
	'Withoutinterwiki'          => [ 'ଇଣ୍ଟରଉଇକିବିନା' ],
];

/** @phpcs-require-sorted-array */
$magicWords = [
	'articlepath'               => [ '0', 'ଲେଖାର_ପଥ', 'ARTICLEPATH' ],
	'currentday'                => [ '1', 'ଏବେକାର_ଦିନ', 'CURRENTDAY' ],
	'currentday2'               => [ '1', 'ଏବେକାର_ଦିନ୨', 'CURRENTDAY2' ],
	'currentdayname'            => [ '1', 'ଏବେକାର_ଦିନ_ନାଆଁ', 'CURRENTDAYNAME' ],
	'currenthour'               => [ '1', 'ଏବେକାର_ଘଣ୍ଟା', 'CURRENTHOUR' ],
	'currentmonth'              => [ '1', 'ଏବେକାର_ମାସ', 'ଏବେର_ମାସ୨', 'CURRENTMONTH', 'CURRENTMONTH2' ],
	'currentmonth1'             => [ '1', 'ଏବେର_ମାସ', 'CURRENTMONTH1' ],
	'currentmonthabbrev'        => [ '1', 'ଏବେକାର_ମାସ_ନାଆଁ_ସଂକ୍ଷିପ୍ତ', 'CURRENTMONTHABBREV' ],
	'currentmonthname'          => [ '1', 'ଏବେକାର_ମାସ_ନାଆଁ', 'CURRENTMONTHNAME' ],
	'currentmonthnamegen'       => [ '1', 'ଏବେକାର_ମାସ_ନାଆଁ_ସାଧାରଣ', 'CURRENTMONTHNAMEGEN' ],
	'currenttime'               => [ '1', 'ଏବେକାର_ସମୟ', 'CURRENTTIME' ],
	'currentversion'            => [ '1', 'ନଗଦ_ରିଭିଜନ', 'CURRENTVERSION' ],
	'currentyear'               => [ '1', 'ଏବେକାର_ବର୍ଷ', 'CURRENTYEAR' ],
	'displaytitle'              => [ '1', 'ଦେଖଣାନାଆଁ', 'DISPLAYTITLE' ],
	'filepath'                  => [ '0', 'ଫାଇଲରାହା:', 'FILEPATH:' ],
	'formatdate'                => [ '0', 'ତାରିଖରପ୍ରକାର', 'formatdate', 'dateformat' ],
	'gender'                    => [ '0', 'ଲିଙ୍ଗ', 'GENDER:' ],
	'grammar'                   => [ '0', 'ବ୍ୟାକରଣ', 'GRAMMAR:' ],
	'hiddencat'                 => [ '1', '_ଲୁଚିଥିବାବିଭାଗ_', '__HIDDENCAT__' ],
	'img_baseline'              => [ '1', 'ବେସଲାଇନ', 'baseline' ],
	'img_border'                => [ '1', 'ବର୍ଡର', 'border' ],
	'img_bottom'                => [ '1', 'ତଳ', 'bottom' ],
	'img_center'                => [ '1', 'କେନ୍ଦ୍ର', 'center', 'centre' ],
	'img_framed'                => [ '1', 'ଫ୍ରେମକରା', 'frame', 'framed', 'enframed' ],
	'img_frameless'             => [ '1', 'ଫ୍ରେମନଥିବା', 'frameless' ],
	'img_left'                  => [ '1', 'ବାଆଁ', 'left' ],
	'img_link'                  => [ '1', 'ଲିଙ୍କ=$1', 'link=$1' ],
	'img_manualthumb'           => [ '1', 'ନଖଦେଖଣା=$1', 'ଦେଖଣା=$1', 'thumbnail=$1', 'thumb=$1' ],
	'img_middle'                => [ '1', 'ମଝି', 'middle' ],
	'img_none'                  => [ '1', 'କିଛି_ନୁହେଁ', 'none' ],
	'img_right'                 => [ '1', 'ଡାହାଣ', 'right' ],
	'img_text_bottom'           => [ '1', 'ଲେଖା-ତଳ', 'text-bottom' ],
	'img_text_top'              => [ '1', 'ଲେଖା-ଉପର', 'text-top' ],
	'img_top'                   => [ '1', 'ଉପର', 'top' ],
	'img_width'                 => [ '1', '$1_ପିକସେଲ', '$1px' ],
	'localday'                  => [ '1', 'Local_ଦିନ', 'LOCALDAY' ],
	'localday2'                 => [ '1', 'ସ୍ଥାନୀୟ_ଦିନ୨', 'LOCALDAY2' ],
	'localdayname'              => [ '1', 'ଦିନ', 'LOCALDAYNAME' ],
	'localhour'                 => [ '1', 'ସ୍ଥାନୀୟ_ଘଣ୍ଟା', 'LOCALHOUR' ],
	'localmonth'                => [ '1', 'ଏବେର_ମାସ୧', 'ସ୍ଥାନୀୟ_ମାସ୨', 'LOCALMONTH', 'LOCALMONTH2' ],
	'localmonth1'               => [ '1', 'ଏବେକାର_ମାସ୧', 'LOCALMONTH1' ],
	'localmonthabbrev'          => [ '1', 'ସ୍ଥାନୀୟ_ମାସର୧_ନାଆଁ_ସଂକ୍ଷିପ୍ତ', 'LOCALMONTHABBREV' ],
	'localmonthname'            => [ '1', 'ମାସ୧ର_ନାଆଁ', 'LOCALMONTHNAME' ],
	'localmonthnamegen'         => [ '1', 'ସ୍ଥାନୀୟ_ମାସ୧_ନାଆଁ_ସାଧାରଣ', 'LOCALMONTHNAMEGEN' ],
	'localtime'                 => [ '1', 'ସ୍ଥାନୀୟ_ସମୟ', 'LOCALTIME' ],
	'localyear'                 => [ '1', 'ସ୍ଥାନୀୟ_ବର୍ଷ', 'LOCALYEAR' ],
	'msg'                       => [ '0', 'ମେସେଜ:', 'MSG:' ],
	'namespace'                 => [ '1', 'ନେମସ୍ପେସ', 'NAMESPACE' ],
	'namespacee'                => [ '1', 'ନେମସ୍ପେସକାରୀ', 'NAMESPACEE' ],
	'newsectionlink'            => [ '1', '_ନୂଆବିଭାଗଲିଙ୍କ_', '__NEWSECTIONLINK__' ],
	'noeditsection'             => [ '0', '_ବଦଳା_ନହେବାଶ୍ରେଣୀ_', '__NOEDITSECTION__' ],
	'nonewsectionlink'          => [ '1', '_ନୂଆ_ବିଭାଗ_ନକରିବା_ଲିଙ୍କ_', '__NONEWSECTIONLINK__' ],
	'numberofactiveusers'       => [ '1', 'ସଚଳ_ବ୍ୟବାହାରକାରୀଙ୍କ_ସଂଖ୍ୟା', 'NUMBEROFACTIVEUSERS' ],
	'numberofadmins'            => [ '1', 'ପରିଛାମାନଙ୍କତାଲିକା', 'NUMBEROFADMINS' ],
	'numberofarticles'          => [ '1', 'ଲେଖା_ସଂଖ୍ୟା', 'NUMBEROFARTICLES' ],
	'numberofedits'             => [ '1', 'ବଦଳ_ସଂଖ୍ୟା', 'NUMBEROFEDITS' ],
	'numberoffiles'             => [ '1', 'ଫାଇଲ_ସଂଖ୍ୟା', 'NUMBEROFFILES' ],
	'numberofpages'             => [ '1', 'ପୃଷ୍ଠା_ସଂଖ୍ୟା', 'NUMBEROFPAGES' ],
	'numberofusers'             => [ '1', 'ବ୍ୟବାହାରକାରୀ_ସଂଖ୍ୟା', 'NUMBEROFUSERS' ],
	'padleft'                   => [ '0', 'ବାଆଁପ୍ୟାଡ଼', 'PADLEFT' ],
	'padright'                  => [ '0', 'ଡାହାଣପ୍ୟାଡ଼', 'PADRIGHT' ],
	'pagename'                  => [ '1', 'ପୃଷ୍ଠା_ନାଆଁ', 'PAGENAME' ],
	'pagenamee'                 => [ '1', 'ପୃଷ୍ଠା_ନାମକାରଣକାରୀ', 'PAGENAMEE' ],
	'pagesize'                  => [ '1', 'ଫରଦଆକାର', 'PAGESIZE' ],
	'plural'                    => [ '0', 'ବହୁବଚନ:', 'PLURAL:' ],
	'protectionlevel'           => [ '1', 'ପ୍ରତିରକ୍ଷାସ୍ତର', 'PROTECTIONLEVEL' ],
	'raw'                       => [ '0', 'କଞ୍ଚା', 'RAW:' ],
	'redirect'                  => [ '0', '#ଲେଉଟାଣି', '#REDIRECT' ],
	'server'                    => [ '0', 'ସର୍ଭର', 'SERVER' ],
	'special'                   => [ '0', 'ବିଶେଷ', 'special' ],
	'subjectspace'              => [ '1', 'ବିଷୟସ୍ପେସ', 'ଲେଖାସ୍ପେସ', 'SUBJECTSPACE', 'ARTICLESPACE' ],
	'tag'                       => [ '0', 'ଟାଗ', 'tag' ],
	'talkspace'                 => [ '1', 'ଟକସ୍ପେସ', 'TALKSPACE' ],
	'talkspacee'                => [ '1', 'ଟକସ୍ପେସକାରୀ', 'TALKSPACEE' ],
	'url_path'                  => [ '0', 'ବାଟ', 'PATH' ],
	'url_query'                 => [ '0', 'ପ୍ରଶ୍ନ', 'QUERY' ],
	'url_wiki'                  => [ '0', 'ଉଇକି', 'WIKI' ],
];

$digitGroupingPattern = "#,##,##0.###";
