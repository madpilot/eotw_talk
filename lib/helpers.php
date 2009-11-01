<?php
  require_once(dirname(__FILE__) . '/htmlpurifier/HTMLPurifier.standalone.php');

  function clean($str)
  {
    // Pre clean to remove Word crud
    $config = array(
      'alt-text' => '',
      'bare' => true,
      'clean' => false,
      'drop-empty-paras' => true,
      'drop-font-tags', true,
      'drop-proprietary-attributes' => true,
      'fix-uri' => true,
      'logical-emphasis' => true,
      'output-xhtml' => true,
      'quote-ampersand' => true,
      'word-2000' => true,
      'show-body-only' => true,
      'indent' => true,
      'indent-spaces' => 2,
      'wrap' => 0,
      'char-encoding' => 'utf8'
    );

    $tidy = new Tidy();
    $tidy->parseString($str, $config, 'UTF8');
    $tidy->cleanRepair();
    $str = $tidy;
    
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache', 'SerializerPath', '/tmp/cache/purify');
    $config->set('AutoFormat', 'RemoveEmpty', true);
    $config->set('Attr' , 'DefaultImageAlt', '');
    $config->set('HTML', 'TidyLevel', 'heavy');
    $config->set('Core', 'Encoding', 'UTF-8');
    $purifier = new HTMLPurifier($config);
    $str = $purifier->purify($str);
    $str = str_replace("<p><br /></p>", "", $str);
    $str = str_replace("<br /></p>", "</p>", $str);
    return $str;
  }

  function traverse_tidy($node, $type)
  {
    $nodes = array();
    if($node != NULL)
    {
      if($node->name == $type)
      {
        array_push($nodes, $node);
      }
      
      if($node->child != NULL)
      {
        foreach($node->child as $child)
        {
          foreach(traverse_tidy($child, $type) as $childNode)
          {
            array_push($nodes, $childNode);
          }
        }
      }
    }
    return $nodes;
  }

  function abstractify($str, $length = 255)
  {
    // First, let's see if there is some obvious paragrahps, then try to find the greatest number
    // of complete paragraphs
    $tidy = new Tidy();
    $tidy->parseString($str);
    
    $body = $tidy->body();
    
    $output = NULL;
    if($body)
    {
      $paragraphs = traverse_tidy($body, "p");
      if(count($paragraphs) > 0)
      {
        // Now, let's find as many complete paragraphs as we can, that doesn't go over 255 characters
        $i = 0;
        while($i < count($paragraphs) && ($i + 1 < count($paragraphs) ? strlen($output) + strlen(strip_tags($paragraphs[$i]->child[0]->value)) + 1 < $length : strlen($output) < $length))
        {
          $output .= strip_tags($paragraphs[$i]->child[0]->value) . " ";
          $i++;
        }
      }
    }
    
    if($output == NULL)
    {
      // No formal paragraphs, so lets just find some text
      $output = substr(strip_tags($str), 0, $length);
    }

    // Finally, make sure we finish on a complete sentence - find the right most period and strip everything after that.
    $lastPeriod = strrpos($output, ".");
   
    if($lastPeriod === FALSE)
    {
      $output = substr($output, 0, $length - 3) . "...";
    }
    elseif($lastPeriod > 0 && $lastPeriod != strlen($output - 1))
    {
      $output = substr($output, 0, $lastPeriod + 1);
    }

    return $output;
  }

  function keywordify($str, $num = 10)
  {
    $stopwords = array(
      "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
      "a's","able","about","above","according","accordingly","across","actually","after","afterwards","again","against","ain't","all",
      "allow","allows","almost","alone","along","already","also","although","always","am","among","amongst","an","and","another","any",
      "anybody","anyhow","anyone","anything","anyway","anyways","anywhere","apart","appear","appreciate","appropriate","are","aren't","around",
      "as","aside","ask","asking","associated","at","available","away","awfully","be","became","because","become","becomes","becoming","been",
      "before","beforehand","behind","being","believe","below","beside","besides","best","better","between","beyond","both","brief","but","by",
      "c'mon","c's","came","can","can't","cannot","cant","cause","causes","certain","certainly","changes","clearly","co","com","come","comes",
      "concerning","consequently","consider","considering","contain","containing","contains","corresponding","could","couldn't","course",
      "currently","definitely","described","despite","did","didn't","different","do","does","doesn't","doing","don't","done","down","downwards",
      "during","each","edu","eg","eight","either","else","elsewhere","enough","entirely","especially","et","etc","even","ever","every","everybody",
      "everyone","everything","everywhere","ex","exactly","example","except","far","few","fifth","first","five","followed","following","follows",
      "for","former","formerly","forth","four","from","further","furthermore","get","gets","getting","given","gives","go","goes","going","gone",
      "got","gotten","greetings","had","hadn't","happens","hardly","","has","hasn't","have","haven't","having","he","he's","hello","help","hence",
      "her","here","here's","hereafter","hereby","herein","hereupon","hers","herself","hi","him","himself","his","hither","hopefully","how",
      "howbeit","however","i'd","i'll","i'm","i've","ie","if","ignored","immediate","in","inasmuch","inc","indeed","indicate","indicated",
      "indicates","inner","insofar","","instead","into","inward","is","isn't","it","it'd","it'll","it's","its","itself","just","keep","keeps",
      "kept","know","knows","known","last","lately","later","latter","latterly","least","less","lest","let","let's","like","liked","likely",
      "little","look","looking","looks","ltd","mainly","many","may","maybe","me","mean","meanwhile","merely","might","more","moreover","most",
      "mostly","much","must","my","myself","name","namely","nd","near","nearly","necessary","need","needs","neither","never","nevertheless",
      "new","","next","nine","no","nobody","non","none","noone","nor","normally","not","nothing","novel","now","nowhere","obviously","of",
      "off","often","oh","ok","okay","old","on","once","one","ones","only","onto","or","other","others","otherwise","ought","our","ours",
      "ourselves","out","outside","over","overall","own","particular","particularly","per","perhaps","placed","please","plus","possible",
      "presumably","probably","provides","que","quite","qv","rather","rd","re","really","reasonably","regarding","regardless","regards",
      "relatively","respectively","right","said","same","saw","say","saying","says","second","secondly","see","seeing","seem","seemed",
      "seeming","seems","seen","self","selves","sensible","sent","serious","seriously","seven","several","shall","she","should","shouldn't",
      "since","six","","so","some","somebody","somehow","someone","","something","sometime","sometimes","somewhat","somewhere","soon","sorry",
      "specified","specify","specifying","still","sub","such","sup","sure","t's","take","taken","tell","tends","th","than","thank","thanks",
      "thanx","that","that's","thats","the","their","theirs","them","themselves","then","thence","there","there's","thereafter","thereby",
      "therefore","therein","theres","thereupon","these","they","they'd","they'll","they're","they've","think","third","this","thorough",
      "thoroughly","those","though","three","through","throughout","thru","thus","to","together","too","took","toward","towards","tried",
      "tries","truly","try","trying","twice","two","un","under","unfortunately","unless","unlikely","until","unto","up","upon","us","use",
      "used","useful","uses","using","usually","value","various","very","via","viz","vs","want","wants","was","wasn't","way","we","we'd",
      "we'll","we're","we've","welcome","well","went","were","weren't","what","what's","whatever","when","whence","whenever","where","where's",
      "whereafter","whereas","whereby","wherein","whereupon","wherever","whether","which","while","whither","who","who's","whoever","whole",
      "whom","whose","why","will","willing","wish","with","within","without","won't","wonder","would","would","wouldn't","yes","yet","you",
      "you'd","you'll","you're","you've","your","yours","yourself","yourselves","zero"
    );
    
    // Tokenise the string, removing any stop words. Drop them into a hash, incrementing the counter if the work is already in there
    // Then take the 10 most common words, and use those for keywords
    $body = preg_replace("/\W/", " ", strip_tags(strtolower($str)));
    $tokens = split(" ", $body);

    $weights = array();
    foreach($tokens as $token)
    {
      if($token != "" && !in_array($token, $stopwords))
      {
        if($weights[$token])
        {
          $weights[$token]++;
        }
        else
        {
          $weights[$token] = 1;
        }
      }
    }
    arsort($weights);
    return array_slice(array_keys($weights), 0, $num);
  }
?>
