<?php

class Af_Lwn extends Plugin {
  function about () {
    return array(
      1.2,
      "Fetch LWN full content (subscribers: set LWN_USER and LWN_PASS in config.php)",
      "spinda, das_j"
    );
  }

  function init ($host) {
    $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
  }

  function hook_article_filter($article) {
    if (strpos($article["link"], "lwn.net/Articles/") !== FALSE) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_NOBODY, false);

      curl_setopt($ch, CURLOPT_USERAGENT, Config::get_user_agent());
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

      if (defined('LWN_USER')) {
        curl_setopt($ch, CURLOPT_URL, 'https://lwn.net/Login/');

        $cookiejar = stream_get_meta_data(tmpfile())['uri'];
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
          "Username=".urlencode(LWN_USER).
          "&Password=".urlencode(LWN_PASS).
          "&target=/");

        curl_exec($ch);

        curl_setopt($ch, CURLOPT_HTTPGET, 1);
      }

      $full_url = preg_replace("/\/rss$/", "", $article["link"]);
      curl_setopt($ch, CURLOPT_URL, $full_url);

      $html = curl_exec($ch);
      curl_close($ch);
      if (defined('LWN_USER')) {
        unlink($cookiejar);
      }

      $doc = new DOMDocument();
      @$doc->loadHTML($html);

      $basenode = false;

      if ($doc) {
        $xpath = new DOMXPath($doc);
        $basenode = $xpath->query('//div[@class="ArticleText"]')->item(0);

        // Fix relative links
        $links = $xpath->query('//a');
        foreach ($links as $link) {
          $href = $link->getAttribute('href');
          if (substr($href, 0, 1) === '/') {
            $link->setAttribute('href', 'https://lwn.net' . $href);
          }
        }

        // Fix relative forms
        $links = $xpath->query('//form');
        foreach ($links as $link) {
          $action = $link->getAttribute('action');
          if (substr($action, 0, 1) === '/') {
            $link->setAttribute('action', 'https://lwn.net' . $action);
          }
        }

        if ($basenode) {
          $article["content"] = $doc->saveXML($basenode);
          $article["link"] = $full_url;
        }
      }
    }

    return $article;
  }

  function api_version () {
    return 2;
  }
}

?>

