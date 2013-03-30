<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 Rob Brazier <rob.brazier@me.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Libraries
 * @package    Libraries
 * @subpackage Libraries
 * @author     Rob Brazier <rob.brazier@me.com>
 * @copyright  2012 Rob Brazier.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    1.0.2
 * @link       http://robbrazier.com
 */

namespace Piwik;
use Config;
use Session;

class Piwik
{
    private static $piwik_url = '';
    private static $site_id = '';
    private static $apikey = '';
    private static $username = '';
    private static $password = '';
    private static $format = '';
    private static $period = '';

// ====================================================================
//
// CHECKERS & GETTERS - Check the config file and retrieve the contents
//
// --------------------------------------------------------------------

    /**
     * date
     * Read config for the period to make API querys about, and translate it into URL-friendly strings
     *
     * @access  private
     * @return  string
     */

    private static function date() {
        static::$period = Config::get('piwik::config.period');
        switch (static::$period) {
            case 'today':
                return '&period=day&date=today';
                break;

            case 'yesterday':
                return '&period=day&date=yesterday';
                break;

            case 'previous7':
                return '&period=range&date=previous7';
                break;

            case 'previous30':
                return '&period=range&date=previous30';
                break;

            case 'last7':
                return '&period=range&date=last7';
                break;

            case 'last30':
                return '&period=range&date=last30';
                break;

            case 'currentweek':
                return '&period=week&date=today';
                break;

            case 'currentmonth':
                return '&period=month&date=today';
                break;

            case 'currentyear':
                return '&period=year&date=today';
                break;

            default:
                return '&period=day&date=yesterday';
                break;
        }
    }

    /**
     * to_https
     * Convert http:// to https:// for tag generation
     *
     * @access  private
     * @return  string
     */

    private static function to_https() {
        $url = static::get_piwik_url();
        if(preg_match('/http:/', $url)){
            return str_replace('http', 'https', $url);
        } else if(preg_match('/https:/', $url)){
            return $url;
        }
    }

    /**
     * to_http
     * Check that the URL is http://
     *
     * @access  private
     * @return  string
     */

    private static function to_http() {
        $url = static::get_piwik_url();
        if(preg_match('/https:/', $url)){
            return str_replace('https', 'http', $url);
        } else if(preg_match('/http:/', $url)){
            return $url;
        }
    }

    /**
     * check_format
     * Check the format as defined in config, and default to json if it is not on the list
     *
     * @access  private
     * @param   string  $override     Override string for the format of the API Query to be returned as
     * @return  string
     */

    private static function check_format($override = null) {
        if($override !== null) {
            static::$format = $override;
        } else {
            static::$format = Config::get('piwik::config.format');
        }
        switch (static::$format) {
            case 'json':
                return 'json';
                break;
            case 'php':
                return 'php';
                break;

            case 'xml':
                return 'xml';
                break;

            case 'html':
                return 'html';
                break;

            case 'rss':
                return 'rss';
                break;

            case 'original':
                return 'original';
                break;

            default:
                return 'json';
                break;
        }

    }

    /**
     * get_site_id
     * Allows access to config.site_id from all functions
     *
     * @access  private
     * @return  string
     */

    private static function get_site_id($id = null) {
        static::$site_id = Config::get('piwik::config.site_id');
        if(isset($id)){
            static::$site_id = $id;
            return static::$site_id;
        } else {
            return static::$site_id;
        }
    }

    /**
     * get_apikey
     * Allows access to config.apikey from all functions
     *
     * @access  private
     * @return  string
     */

    private static function get_apikey() {
        static::$apikey = Config::get('piwik::config.api_key');
        static::$username = Config::get('piwik::config.username');
        static::$password = md5(Config::get('piwik::config.password'));

        if(empty(static::$apikey) && !empty(static::$username) && !empty(static::$password)){
            $url = static::get_piwik_url().'/index.php?module=API&method=UsersManager.getTokenAuth&userLogin='.static::$username.'&md5Password='.static::$password.'&format='.static::check_format();
            if(!Session::has('apikey')) Session::put('apikey', static::get_decoded($url));
            static::$apikey = Session::get('apikey');
            return static::$apikey;
        } else if(!empty(static::$apikey)) {
            return static::$apikey;
        } else {
            echo '<strong style="color:red">You must enter your API Key or Username/Password combination to use this bundle!</strong><br/>';
        }
    }

    /**
     * get_piwik_url
     * Allows access to config.piwik_url from all functions
     *
     * @access  private
     * @return  string
     */

    private static function get_piwik_url() {
        static::$piwik_url = Config::get('piwik::config.piwik_url');
        return static::$piwik_url;
    }

    private static function _get($url) {
      $ch = curl_init();
      $timeout = 5;
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
    }

    /**
     * get_decoded
     * Decode the format to usable PHP arrays/objects
     *
     * @access  private
     * @param   string  $url   URL to decode (declared within other functions)
     * @return  array
     */

    private static function get_decoded($url, $format = null){
        switch (static::check_format($format)) {
            case 'json':
                return json_decode(static::_get($url));
                break;
            case 'php':
                return unserialize(static::_get($url));
                break;

            case 'xml':
                //$xml = unserialize(file_get_contents($url));
                return 'Not Supported as of yet';
                break;

            case 'html':
                return static::_get($url);
                break;

            case 'rss':
                return 'Not supported as of yet';
                break;

            case 'original':
                return file_get_contents($url);
                break;

            default:
                return file_get_contents($url);
                break;
        }
    }

    /**
     * url_from_id
     * Fetches the URL from Site ID
     *
     * @access  private
     * @param   string  $id   Override for ID, so you can specify one rather than fetching it from config
     * @return  string
     */

    private static function url_from_id($id = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=SitesManager.getSiteUrlsFromId&idSite='.static::get_site_id($id).static::date().'&format=php&token_auth='.static::get_apikey();
        $gd = static::get_decoded($url, 'php');
        return $gd[0][0];
    }

// ====================================================================
//
// API Queries
//
// --------------------------------------------------------------------

    /**
     * actions
     * Get actions (hits) for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function actions($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=VisitsSummary.getActions&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * downloads
     * Get file downloads for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function downloads($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Actions.getDownloads&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * keywords
     * Get search keywords for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function keywords($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Referers.getKeywords&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * last_visits
     * Get information about last 10 visits (ip, time, country, pages, etc.)
     *
     * @access  public
     * @param   int     $count      Limit the number of visits returned by $count
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function last_visits($count, $format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Live.getLastVisitsDetails&idSite='.static::get_site_id().static::date().'&filter_limit='.$count.'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * last_visits_parsed
     * Get information about last 10 visits (ip, time, country, pages, etc.) in a formatted array with GeoIP information if enabled
     *
     * @access  public
     * @param   int     $count      Limit the number of visits returned by $count
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function last_visits_parsed($count, $format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Live.getLastVisitsDetails&idSite='.static::get_site_id().static::date().'&filter_limit='.$count.'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        $visits = static::get_decoded($url, $format);

        $data = array();
        foreach($visits as $v)
        {
            // Get the last array element which has information of the last page the visitor accessed
            switch (static::check_format($format)) {
            case 'json':
                $count = count($v->actionDetails) - 1;
                $page_link = $v->actionDetails[$count]->url;
                $page_title = $v->actionDetails[$count]->pageTitle;

                // Get just the image names (API returns path to icons in piwik install)
                $flag = explode('/', $v->countryFlag);
                $flag_icon = end($flag);

                $os = explode('/', $v->operatingSystemIcon);
                $os_icon = end($os);

                $browser = explode('/', $v->browserIcon);
                $browser_icon = end($browser);

                $data[] = array(
                  'time' => date("M j Y, g:i a", $v->lastActionTimestamp),
                  'title' => $page_title,
                  'link' => $page_link,
                  'ip_address' => $v->visitIp,
                  'provider' => $v->provider,
                  'country' => $v->country,
                  'country_icon' => $flag_icon,
                  'os' => $v->operatingSystem,
                  'os_icon' => $os_icon,
                  'browser' => $v->browserName,
                  'browser_icon' => $browser_icon,
                );
                break;
            case 'php':
                $count = count($v['actionDetails']) - 1;
                $page_link = $v['actionDetails'][$count]['url'];
                $page_title = $v['actionDetails'][$count]['pageTitle'];

                // Get just the image names (API returns path to icons in piwik install)
                $flag = explode('/', $v['countryFlag']);
                $flag_icon = end($flag);

                $os = explode('/', $v['operatingSystemIcon']);
                $os_icon = end($os);

                $browser = explode('/', $v['browserIcon']);
                $browser_icon = end($browser);

                $data[] = array(
                  'time' => date("M j Y, g:i a", $v['lastActionTimestamp']),
                  'title' => $page_title,
                  'link' => $page_link,
                  'ip_address' => $v['visitIp'],
                  'provider' => $v['provider'],
                  'country' => $v['country'],
                  'country_icon' => $flag_icon,
                  'os' => $v['operatingSystem'],
                  'os_icon' => $os_icon,
                  'browser' => $v['browserName'],
                  'browser_icon' => $browser_icon,
                );
                break;

            case 'xml':

                break;

            case 'html':

                break;

            case 'rss':

                break;

            case 'original':

                break;

            default:
                $count = count($v->actionDetails) - 1;
                $page_link = $v->actionDetails[$count]->url;
                $page_title = $v->actionDetails[$count]->pageTitle;

                // Get just the image names (API returns path to icons in piwik install)
                $flag = explode('/', $v->countryFlag);
                $flag_icon = end($flag);

                $os = explode('/', $v->operatingSystemIcon);
                $os_icon = end($os);

                $browser = explode('/', $v->browserIcon);
                $browser_icon = end($browser);

                $data[] = array(
                  'time' => date("M j Y, g:i a", $v->lastActionTimestamp),
                  'title' => $page_title,
                  'link' => $page_link,
                  'ip_address' => $v->visitIp,
                  'provider' => $v->provider,
                  'country' => $v->country,
                  'country_icon' => $flag_icon,
                  'os' => $v->operatingSystem,
                  'os_icon' => $os_icon,
                  'browser' => $v->browserName,
                  'browser_icon' => $browser_icon,
                );
                break;
            }

        }
        return $data;
    }

    /**
     * actions
     * Get outlinks for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function outlinks($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Actions.getOutlinks&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * page_titles
     * Get page visit information for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function page_titles($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Actions.getPageTitles&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * search_engines
     * Get search engine referer information for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function search_engines($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Referers.getSearchEngines&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * unique_visitors
     * Get unique visitors for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function unique_visitors($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=VisitsSummary.getUniqueVisitors&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * visits
     * Get all visits for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function visits($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=VisitsSummary.getVisits&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * websites
     * Get refering websites (traffic sources) for the specific time period
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */
    public static function websites($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=Referers.getWebsites&idSite='.static::get_site_id().static::date().'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * tag
     * Get javascript tag for use in tracking the website
     *
     * Note: Works best when using PHP as the format
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  string
     */

    public static function tag() {
        $tag =
'<!-- Piwik -->
<script type="text/javascript">
var _paq = _paq || [];
(function(){ var u=(("https:" == document.location.protocol) ? "'.static::to_https().'/" : "'.static::to_http().'/");
_paq.push([\'setSiteId\', '.static::get_site_id().']);
_paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
_paq.push([\'trackPageView\']);
_paq.push([\'enableLinkTracking\']);
var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0]; g.type=\'text/javascript\'; g.defer=true; g.async=true; g.src=u+\'piwik.js\';
s.parentNode.insertBefore(g,s); })();
</script>
<!-- End Piwik Code -->';

        return $tag;
    }

    /**
     * seo_rank
     * Get SEO Rank for the website
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */

    public static function seo_rank($id = null, $format = 'json') { // PHP doesn't seem to work with this, so defaults to JSON
        $url = static::get_piwik_url().'/index.php?module=API&method=SEO.getRank&url='.static::url_from_id($id).'&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * version
     * Get Version of the Piwik Server
     *
     * @access  public
     * @param   string  $format     Override string for the format of the API Query to be returned as
     * @return  array
     */

    public static function version($format = null) {
        $url = static::get_piwik_url().'/index.php?module=API&method=API.getPiwikVersion&format='.static::check_format($format).'&token_auth='.static::get_apikey();
        return static::get_decoded($url, $format);
    }

    /**
     * custom
     * Create a Custom API Query
     *
     * @access  public
     * @param   string           $method        The method to use to query Piwik API
     * @param   array            $arguments     Array of extra arguments to add to the API Query
     * @param   boolean integer  $id            This is either a boolean value, 'true' displaying the site ID as declared in config, and also you can enter custom Site IDs
     * @param   boolean          $period        Determines whether you want the period & date in the query URL
     * @param   string           $format        Override string for the format of the API Query to be returned as
     * @return  array
     */

    public static function custom($method, $arguments = array(), $id = false, $period = false, $format = null) {
        if($arguments == null){
            $arguments = array();
        }
        if(isset($method)){
            $url = static::get_piwik_url().'/index.php?module=API&method='.$method;
            foreach($arguments as $key=>$value){
                $url .= '&'.$key.'='.$value;
            }
            if($id){
                $url .= '&idSite='.static::get_site_id($id);
            }
            if($period = true){
                $url .= static::date();
            }
            $url .= '&format='.static::check_format($format).'&token_auth='.static::get_apikey();
            return static::get_decoded($url, $format);
        }
    }

    /**
     * set_site_id
     * (Temporarily) Set Site ID without changing the id in config
     *
     * @access  public
     * @param   integer  $id    ID to override the Site ID in the config file
     * @return  null
     */

    public static function set_site_id($id) {
        Config::set('piwik::config.site_id', $id);
    }

}