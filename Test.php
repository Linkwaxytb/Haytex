<?php
// This is a user-facing page
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once '../../users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
$hooks = getMyHooks();
includeHook($hooks, 'pre');

if (!empty($_POST['uncloak'])) {
    logger($user->data()->id, 'Cloaking', 'Attempting Uncloak');
    if (isset($_SESSION['cloak_to'])) {
        $to = $_SESSION['cloak_to'];
        $from = $_SESSION['cloak_from'];
        unset($_SESSION['cloak_to']);
        $_SESSION[Config::get('session/session_name')] = $_SESSION['cloak_from'];
        unset($_SESSION['cloak_from']);
        logger($from, 'Cloaking', 'uncloaked from '.$to);
        $cloakHook =  getMyHooks(['page'=>'cloakEnd']);
        includeHook($cloakHook,'body');
        usSuccess("You are now you");
        Redirect::to($us_url_root.'users/admin.php?view=users');
    } else {
        usError("Something went wrong. Please login again");
        Redirect::to($us_url_root.'users/logout.php');
    }
}

//dealing with if the user is logged in
if ($user->isLoggedIn() || !$user->isLoggedIn() && !hasPerm(2)) {
    if (($settings->site_offline == 1) && (!in_array($user->data()->id, $master_account)) && ($currentPage != 'login.php') && ($currentPage != 'maintenance.php')) {
        $user->logout();
        logger($user->data()->id, 'Errors', 'Sending to Maint');
        Redirect::to($us_url_root.'users/maintenance.php');
    }
}
$grav = fetchProfilePicture($user->data()->id);
$get_info_id = $user->data()->id;
// $groupname = ucfirst($loggedInUser->title);
$raw = date_parse($user->data()->join_date);
$signupdate = $raw['month'].'/'.$raw['day'].'/'.$raw['year'];
$userdetails = fetchUserDetails(null, null, $get_info_id); //Fetch user details
if($user->isLoggedIn()) { $thisUserID = $user->data()->id;} else { $thisUserID = 0; }
if(!isset($_GET['id'])){
	$userID = $user->data()->id;
}else{
	$userID = Input::get('id');
}

if(isset($userID))
	{
	$userQ = $db->query("SELECT * FROM profiles LEFT JOIN users ON user_id = users.id WHERE user_id = ?",array($userID));
	$thatUser = $userQ->first();

	if($thisUserID == $userID)
		{
		$editbio = ' <small><a href="edit_profile.php">Edit Bio</a></small>';
		}
	else
		{
		$editbio = '';
		}

	$ususername = ucfirst($thatUser->username);
	$usbio = html_entity_decode($thatUser->bio);
	}
else
	{
	$ususername = '404';
	$usbio = 'User not found';
	$useravatar = '';
	$editbio = ' <small><a href="/">Go to the homepage</a></small>';
	}
?>
<?php
    if (isset($_POST['series_id']) && isset($_POST['season_number'])) {
        $series_id = $_POST['series_id'];
        $season_number = $_POST['season_number'];
        $api_key     = '632577cc36b03c82c4167164f4edd49f';
        $url_princ   = "https://api.themoviedb.org/3/tv/$series_id?api_key=$api_key&append_to_response=credits&language=fr";
        $url         = "https://api.themoviedb.org/3/tv/$series_id/season/$season_number?api_key=$api_key&language=fr";
        $json_princ  = file_get_contents($url_princ);
        $json        = file_get_contents($url);
        $data        = json_decode($json, true);
        $serie_data  = json_decode($json_princ, true);
        
       

        $serie_name  = $serie_data['name']; // récupérer le nom de la série
        $serie_overview = $serie_data['overview']; // synopsis de la série
        $serie_total_episodes = $serie_data['number_of_episodes']; // nombre total d'épisodes
        $serie_total_seasons = $serie_data['number_of_seasons']; // nombre total de saisons
        $serie_poster_path = $serie_data['poster_path']; // image de présentation de la série
        $serie_backdrop_path = $serie_data['backdrop_path']; // image de fond de la série
        $prop        = 'https://api.themoviedb.org/3/tv/' . $movie_id . '/similar?api_key=' . $api_key.'&language=fr';
        $note        = $serie_data['vote_average'];
        $genres        = $serie_data['genres'];
		$actors        = $serie_data['credits']['cast'];
        $crew = $serie_data['credits']['crew'];
        $directors = array();
        		$video_url   = "https://api.themoviedb.org/3/tv/$serie_id/videos?api_key=$api_key&language=fr";
        $json_data   = file_get_contents($prop);
		$json_video  = file_get_contents($video_url);
		$data_video  = json_decode($json_video, true);
        $dataprop    = json_decode($json_data, true);
		$trailer_key = '';
		foreach ($data_video['results'] as $result)
		{
			if ($result['type'] == 'Trailer' && $result['site'] == 'YouTube')
			{
				$trailer_key = $result['key'];
				break;
			}
		}
         // transformer $lien_page en minuscules
    $lien_page = strtolower($serie_name);

    // supprimer les accents et les ponctuations
    $lien_page = preg_replace('/[\p{P}\p{S}]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', $lien_page));

    // remplacer les espaces par des underscores
    $lien_page = str_replace(' ', '_', $lien_page);
		$trailer_url   = 'https://www.youtube.com/watch?v=' . $trailer_key;
        $release_date  = $data['release_date'];
        $date          = date('Y', strtotime($release_date));

        // construction de l'URL de l'image de présentation et de l'image de fond
        $serie_poster_url = 'https://image.tmdb.org/t/p/w500' . $serie_poster_path;
        $serie_backdrop_url = 'https://image.tmdb.org/t/p/w1280' . $serie_backdrop_path;
        $season_number = $_POST['season_number'];
        $liste = "<section>
<div class='Top AAIco-list'>
<div class='Title'>Sélection épisode</div></br></br>

<div class='seasons aa-crd' x-data='{ tab: 0 }'><div class='seasons-bx' @click='tab = 0'><div :class='{ 'seasons-tt aa-crd-lnk': true, 'on': tab == 0 }' x-clock='' class='seasons-tt aa-crd-lnk on'><figure><img src='$serie_poster_url' loading='lazy' alt='$serie_name Saison    d $season_number'></figure><div><p>Saison <span>$season_number</span> <i class='fa-chevron-down'></i></p><span class='date'>13 Episodes </span></div></div>

<ul class='seasons-lst anm-a'>
";

        if (isset($data['episodes'])) {
            $links = array(); // tableau associatif pour stocker les liens pour chaque épisode

            // afficher un formulaire pour chaque épisode
            echo '<form method="post">';
            foreach ($data['episodes'] as $index => $episode) {
    $episode_number = $episode['episode_number'];
    $episode_name   = $episode['name']; 
    $image_url      = "https://image.tmdb.org/t/p/w500" . $episode["still_path"];

    // Récupérer la date de l'épisode et la formater
    $air_date = $episode['air_date'];
    $formatted_date = date('j M. Y', strtotime($air_date));
            $liste .="<li><div><div><figure class='fa-play-circle'><img class='brd1 poa' src='$image_url' loading='lazy' alt='Episode $episode_number'></figure><h3 class='title'><span>S$season_number-E$episode_number</span> $episode_name - VF</h3></div><div><span class='date'>$formatted_date</span><a href='http://haytex.epizy.com/series/$lien_page/". $season_number ."x". $episode_number ."vf' class='btn sm rnd'>Regarder l'épisode</a></div></div></li>";
                

                echo "<h2>Episode $episode_number: $episode_name</h2>";
                echo '<label for="link_' . $episode_number . '">Lien uqload pour cet épisode :</label>';
                echo '<input type="text" name="links[' . $episode_number . ']" id="link_' . $episode_number . '" placeholder="Entrez le lien uqload pour cet épisode" required/>';

                $links = $_POST['links'];
                foreach ($links as $episode_number => $link) {
$test = "
<html lang=\"en-FR\"><head>
  <meta charset=\"utf-8\">
  <title>$serie_name EP$episode_number S$season_number sur Haytex</title>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/jquery.js\" id=\"funciones_public_jquery-js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/owl.carousel.min.js\" id=\"funciones_public_carousel-js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/sol.js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/fonction.js\"></script>
  <link rel=\"stylesheet\" href=\"http://haytex.epizy.com/css/style.css\">
  <link rel=\"icon\" href=\"http://haytex.epizy.com/img/Haytex_logo.png\">
  <link rel=\"stylesheet\" id=\"TOROFLIX_Theme-css\" href=\"http://haytex.epizy.com/css/public.css?ver=1.2.0\" type=\"text/css\" media=\"all\">
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/jquery.min.js?ver=3.6.1\" id=\"jquery-core-js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/script.js\" id=\"jquery-core-js\"></script>
  <link rel=\"stylesheet\" href=\"http://haytex.epizy.com/css/style1.css?ver=8.4\">
  <link rel=\"stylesheet\" id=\"pub.min.css-css\" href=\"http://haytex.epizy.com/css/Test.css?ver=1647590673\" type=\"text/css\" media=\"all\">
 <link rel=\"stylesheet\" href=\"https://allmoviesforyou.net/wp-content/themes/toroflix/public/css/toroflix-public.css?ver=1.2.0\" type=\"text/css\" media=\"all\">
  <script type=\"text/javascript\">
    /* <![CDATA[ */
    var toroflixPublic = {};
    /* ]]> */
  </script>
  <script>
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://haytex.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
</script>
<script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/jquery.js\" id=\"funciones_public_jquery-js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/owl.carousel.min.js\" id=\"funciones_public_carousel-js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/sol.js\"></script>
  <script type=\"text/javascript\" src=\"http://haytex.epizy.com/js/fonction.js\"></script>
</head>




<body id=\"Tf-Wp\" class=\"home blog wp-custom-logo BdGradient aa-prefix-allmo-\">
  <?php include('../../php/template/menu.php')?>
    
    <div class=\"Body\">
<div class=\"TPost A D\">
<div class=\"Container\">
<div class=\"VideoPlayer\">
<div id=\"VideoOption01\" class=\"Video on\">
<iframe src=\"https://uqload.co/embed-y2x1vp1v2yxm.html\" allowfullscreen frameborder=\"0\" ></iframe>
</div>
<div class=\"navepi tagcloud\">
<a href=\"#r\" class=\"prev off\"><span>Episode précédent </span></a>
<a href=\"http://haytex.epizy.com/series/the-last-of-us/\" class=\"list prev\"><span>Episodes</span></a>
<a href=\"http://haytex.epizy.com/series/the-last-of-us/1x2vf\" class=\"next\"> <span>Episode suivant</span> </a>
</div> </div>
<div class=\"Image\">
<figure class=\"Objf\"><img src=\"https://m.media-amazon.com/images/M/MV5BMzViMDY5YTUtM2ZhYS00MWQxLWIyZTctMzc3ZjZlMTM3MzUwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_FMjpg_UX1280_.jpg\" alt=\"Background\"></figure>
</div>
</div>
</div> <div class=\"Body\"><div class=\"Main Container\"> <div class=\"TpRwCont \"> <main> <article class=\"TPost A\">
<header class=\"Container\">
<div class=\"TPMvCn\">
<h1 class=\"Title\" style=\"color:white\">The Last of Us EP01 S01 VF</h1>
<div class=\"Info\">
<span class=\"Date\">2022</span> <span class=\"Time\">Durée: 53 min</span> <a href=\"http://haytex.epizy.com/series/the-last-of-us/\">Tous les épisodes</a>
</div>
<div class=\"Description\">
<span>Réalisation:</span
        ><span
          ><a class=\"por z3\" href=\"/realisateurs/craig-mazin\"
            >Craig Mazin</a
          ></span
        >
<p class=\"Cast\">
<span>Casting principal:</span> 
<a href=\"/cast/pedro-pascal\">Pedro Pascal</a>
<span class=\"dot-sh\">,</span> 
<a href=\"/cast/bella-ramsey\">Bella Ramsey</a>
<span class=\"dot-sh\">,</span> 
<a href=\"/cast/gabriel-luna/\">Gabriel Luna</a>
<span class=\"dot-sh\">,</span> 
<a href=\"/cast/jeffrey-pierce\">Jeffrey Pierce</a>...</p>

<p class=\"Genre\"><span>Genre:</span> <a href=\"/cat/action\">Action</a>,
          <a href=\"/cat/aventure\">Aventure</a>,
          <a href=\"/cat/drame\">Drame</a></span
        ></p></div>
</div>
</header>
</article>

<!--<épisodes>-->
<?php include(\"../../php/series/episodes/the-last-of-us-1.php\")?>
<!--<fin épisodes>-->
<section>
              <div class=\"Top AAIco-chat\">
                <div class=\"Title\">Commentaires</div>
              </div>
              <div class=\"Comment Wrt\">
                
                <div id=\"disqus_thread\"></div>

              </div>
            </section>
</main>  

<!--<sidebar>-->
<?php include(\"../../php/template/side.php\")?>
<!--<fin sidebar>-->
";
               
                
                

// créer une nouvelle page pour chaque épisode
    $filename = 'episode_' . $episode_number . '.html';
    $handle   = fopen($filename, 'w');
    fwrite($handle, $test);
    };

$liste .="</ul></div></section>";
// créer une nouvelle page pour chaque épisode
                $filenom =  $lien_page. "-". $season_number .'.php';
                $handle   = fopen($filenom, 'w');
                fwrite($handle, $liste);

                // fermer le fichier
                fclose($handle);








            $page_princ = "
                
<html lang='en-FR'><head>
  <meta charset='utf-8'>
  <title>$serie_name sélection épisode sur Haytex</title>
  <script type='text/javascript' src='http://haytex.epizy.com/js/jquery.js' id='funciones_public_jquery-js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/owl.carousel.min.js' id='funciones_public_carousel-js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/sol.js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/fonction.js'></script>
  <link rel='stylesheet' href='http://haytex.epizy.com/css/style.css'>
  <link rel='icon' href='http://haytex.epizy.com/img/Haytex_logo.png'>
  <link rel='stylesheet' id='TOROFLIX_Theme-css' href='http://haytex.epizy.com/css/public.css?ver=1.2.0' type='text/css' media='all'>
  <script type='text/javascript' src='http://haytex.epizy.com/js/jquery.min.js?ver=3.6.1' id='jquery-core-js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/script.js' id='jquery-core-js'></script>
  <link rel='stylesheet' href='http://haytex.epizy.com/css/style1.css?ver=8.4'>
  <link rel='stylesheet' id='pub.min.css-css' href='http://haytex.epizy.com/css/Test.css?ver=1647590673' type='text/css' media='all'>
 <link rel='stylesheet' href='https://allmoviesforyou.net/wp-content/themes/toroflix/public/css/toroflix-public.css?ver=1.2.0' type='text/css' media='all'>
  <script type='text/javascript'>
    /* <![CDATA[ */
    var toroflixPublic = {};
    /* ]]> */
  </script>
  <script>
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://haytex.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
</script>
<script type='text/javascript' src='http://haytex.epizy.com/js/jquery.js' id='funciones_public_jquery-js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/owl.carousel.min.js' id='funciones_public_carousel-js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/sol.js'></script>
  <script type='text/javascript' src='http://haytex.epizy.com/js/fonction.js'></script>
</head>






<body id='Tf-Wp' class='home blog wp-custom-logo BdGradient aa-prefix-allmo-'>
<!--<Menu>-->
  <?php include('../../php/template/menu.php')?>
<!--<Fin Menu>-->

<div class='Body'>
<div class='MovieListSldCn'>
<article class='TPost A'>
<header class='Container'>
<div class='TPMvCn'>
<h1 class='Title'>$serie_name</h1>
<p class='SubTitle'><span>$serie_total_seasons</span> Saison(s) - <span>$serie_total_episodes</span> Episodes</p>
<div class='entry-meta'>
<span class='rating fa-star'><span>$note/10</span></span><span class='year'>2023</span></div>
<div class='Description'>
<p>$serie_overview</p>";


//Réalisateurs
        foreach ($crew as $member) {
            if ($member['job'] == 'Director') {
                $directors[] = $member['name'];
            }
        }
        if (!empty($directors)) {
            $page_princ .= "<p class='Director'><span>Réalisation:</span> ";
            foreach ($directors as $director) {
                $page_princ .= "<a href='http://haytex.epizy.com/director/" . urlencode($director) . "' target='_blank'>" . $director . "</a>, ";
            }
            $page_princ = rtrim($page_princ, ', ');
            $page_princ .= "</p>";
        }
		//Acteurs
		$page_princ .= "<p class='Cast Cast-sh oh'>
        <span>Casting principal:</span> ";
        foreach ($actors as $actor) {
            $page_princ .= "<a href='http://haytex.epizy.com/casting/" . $actor['id'] . "' target='_blank'>" . $actor['name'] . "</a> ";
        }
        $page_princ = rtrim($page_princ, ', ');
        $page_princ .= "</p>";
		$page_princ .= '<p class="Genre">
                <span>Genres:</span> ';
        foreach ($genres as $genre) {
            $page_princ .= '<a href="http://haytex.epizy.com/genre/'.$genre['name'].'" target="_blank">'.$genre['name'].'</a>, ';
        }
		$page_princ     = rtrim($page_princ, ', ');
        $page_princ .= "</p>";

$page_princ .="<a href='javascript:void(0)' onclick='window.open ('$trailer_url', 'Youtube', 'toolbar=0, status=0, width=650, height=450');' id='watch-trailer' class='Button TPlay AAIco-play_circle_outline'><strong>Bande-Annonce</strong></a>
</div>
<div class='Image'>
<figure class='Objf'><img loading='lazy' class='TPostBg' src='$serie_backdrop_url' alt='Background'></figure>
</div>
</header>
</article>
</div>
<div class='Body'>
<div class='Main Container'>
 <div class='TpRwCont '>

<!--<épisodes>-->
<?php include('$lien_page-$season_number.php')?>
<!--<fin épisodes>-->

<section>
<div class='Top AAIco-chat'>
<div class='Title'>Commentaires</div>
</div>
<div class='Comment Wrt'>
<div id='disqus_thread'><iframe id='dsq-app8598' name='dsq-app8598' allowtransparency='true' frameborder='0' scrolling='no' tabindex='0' title='Disqus' width='100%' src='https://disqus.com/embed/comments/?base=default&amp;f=haytex&amp;t_u=http%3A%2F%2Fhaytex.epizy.com%2Ffilms%2Fklaus&amp;t_d=Regarder%20Klaus%20sur%20Haytex&amp;t_t=Regarder%20Klaus%20sur%20Haytex&amp;s_o=desc#version=1b064540b1d3262ce7bcdf11f9ce2e17' style='width: 1px !important; min-width: 100% !important; border: none !important; overflow: hidden !important; height: 649px !important;' horizontalscrolling='no' verticalscrolling='no'></iframe></div>
</div>
</section>
</main>
 <!--<sidebar>-->
          <aside>

           <?php include('../../php/template/side.php')?>
        <section>
        <div class='Top AAIco-movie_filter'>
            <div class='Title'>Vous aimerez aussi</div>
          </div>
          <div class='MovieListTop owl-carousel Serie'>
          ";
        foreach ($dataprop['results'] as $serie)
        {
            $title = $serie['title'];
            $poster_path = $serie['poster_path'];
            $poster_url = 'https://image.tmdb.org/t/p/w500/' . $poster_path;
            $serie_name = $serie['name'];
            $serie_url = 'http://haytex.epizy.com/films/' . $serie_name;

            $page_princ .= "
        <div class='TPostMv'>
            <div class='TPost B'>
                <a href='http://haytex.epizy.com/films/" . $serie_url . "'>
                    <div class='Image'>
                        <figure class='Objf TpMvPlay AAIco-play_arrow'>
                            <img loading='lazy' class='owl-lazy' data-src='" . $poster_url . "' alt='" . $title . "' />
                        </figure>
                        <span class='Qlty'>SERIE</span>
                    </div>
                    <h2 class='Title'>" . $title . "</h2>
                </a>
            </div>
        </div>";
        };
    
        $page_princ.="
        </section>
      </div>
    </div>
    
    <footer class='Footer'>
      <div class='Bot'>
        <div class='Container'>
          <p>2022 Copyright © Haytex Tous Droits Réservés</p>
        </div>
      </div>
    </footer>
  </div>


<div id='disqus_recommendations'></div>
                    <script>
(function() { // REQUIRED CONFIGURATION VARIABLE: EDIT THE SHORTNAME BELOW
var d = document, s = d.createElement('script'); // IMPORTANT: Replace EXAMPLE with your forum shortname!
s.src ='https://haytex.disqus.com/recommendations.js'; s.setAttribute('data-timestamp', +new Date());
(d.head || d.body).appendChild(s);
})();
</script>
<!--NE PAS TOUCHER-->
  <style id='toronites_style_css' type='text/css'> :root{ --body: #0b0c0c; --text: #bfc1c3; --link: #ffffff; --primary: 690DAB; } </style>
  <style type='text/css' id='wp-custom-css'>
    			.AZList {
        font-size: 0;
        margin: 0 -5px 1.5rem;
        text-align: center;
    	display: true;
    }
    .Button.btn-report {
        background-color: #d63638!important;
    }

    .MovieListSld .owl-dots {
        position: absolute;
        left: 0;
        right: 0;
        margin: auto;
        bottom: 3rem!important;
    }
    .owl-dots>div>span{display:block;background-color:currentColor;margin-top:0px}

    .owl-dots>div>span {
        background-color: #fff;
        background-color: hsla(0,0%,100%,.3);
        box-shadow: 1px 1px 4px rgb(0 0 0 / 40%);
        display: block;
        margin-right: 7px;
        overflow: hidden;
        text-indent: -9999px;
        transition: height .3s ease;
        height: 17px;
        */: ;
        width: 20px;
    }


    ::-webkit-scrollbar {
        width: 10px;     /* Tamaño del scroll en vertical */
        height: 10px;    /* Tamaño del scroll en horizontal */
        display: ;  /* Ocultar scroll */
    }

    /* Ponemos un color de fondo y redondeamos las esquinas del thumb */
    ::-webkit-scrollbar-thumb {
        background: #0064ef;
        border-radius: 4px;
    }

    /* Cambiamos el fondo y agregamos una sombra cuando esté en hover */
    ::-webkit-scrollbar-thumb:hover {
        background: #0064ef;
        box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.2);
    }

    /* Cambiamos el fondo cuando esté en active */
    ::-webkit-scrollbar-thumb:active {
        background-color: #0064ef;
    }

    /* Ponemos un color de fondo y redondeamos las esquinas del track */
    ::-webkit-scrollbar-track {
        background: #1c1c1a;
        border-radius: px;
    }

    /* Cambiamos el fondo cuando esté en active o hover */
    ::-webkit-scrollbar-track:hover,
    .container::-webkit-scrollbar-track:active {
      background: #1c1c1a;
    }
    .custom-logo{
    height:50px
    }
    .custom-logo{
    	width:150px
    }
    /*.btjemE {
        margin-top: -100px;
        padding: 110px 0px 26px;
        display: grid;
        gap: 35px;
        grid-template-columns: repeat(5, minmax(0px, 1fr));
    }

    /*disney*/
    .dWjUC:hover {
        box-shadow: rgb(0 0 0 / 80%) 0px 40px 58px -16px, rgb(0 0 0 / 72%) 0px 30px 22px -10px;
        transform: scale(1.05);
        border-color: rgba(249, 249, 249, 0.8);
    }

    .dWjUC {
        padding-top: 56.25%;
        border-radius: 10px;
        box-shadow: rgb(0 0 0 / 69%) 0px 26px 30px -10px, rgb(0 0 0 / 73%) 0px 16px 10px -10px;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        transition: all 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s;
        border: 3px solid rgba(249, 249, 249, 0.1);
    }

    .dWjUC img {
        inset: 0px;
        display: block;
        height: 100%;
        object-fit: cover;
        opacity: 1;
        position: absolute;
        transition: opacity 500ms ease-in-out 0s;
        width: 100%;
        z-index: 1;
    }

    /*video disney*/
    .dWjUC:hover video {
        opacity: 1;
    }
    .dWjUC video {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0px;
        opacity: 0;
        z-index: 0;
    }

    /*pixar*/
    .dWjUC {
        padding-top: 56.25%;
        border-radius: 10px;
        box-shadow: rgb(0 0 0 / 69%) 0px 26px 30px -10px, rgb(0 0 0 / 73%) 0px 16px 10px -10px;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        transition: all 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94) 0s;
        border: 3px solid rgba(249, 249, 249, 0.1);
    }

    .TPost .Image figure {
        position: relative;
        padding-top: 150%;
        border-radius: 20px;
    }



    @media screen and (min-width: 62em){
    	.TPost.B:hover .TPMvCn {
        /*opacity: 5;*/
        visibility: collapse;
        /*transform: scale(1);*/
    }
    }

    .TPost.B:hover .Image figure img {
        opacity: .6;
    }


    .TPost.B:hover .Image figure img {
        /*opacity: .6;*/
        transform: scale(1.6);
        filter: blur(5px);
    }



    .containerr__cardss {
        /* max-width: 1200px; */
        /* margin: auto; */
        /* margin-top: 100px; */
        display: flex;
        /* flex-wrap: wrap; */
        justify-content: center;
    }

    .cardd {
        width: 303px;
        margin: -1px;
        transition: all 300ms;
        margin-top: -40px;
    }

    .cardd:hover{
        width: 350px;
    }

    .cardd .cover {
        width: 100%;
        height: 250px;
        position: relative;
        overflow: hidden;
    }

    .cardd .cover img{
        width: 250px;
        display: block;
        margin: auto;
        position: relative;
        top: 40px;
        z-index: 1;
        filter: drop-shadow(5px 5px 4px rgba(0,0,0,0.5));
        transition: all 400ms;
    }

    .cardd:hover .cover img{
        top: 0px;
        filter: none;
    }

    .cardd .img__back{
        width: 100%;
        height: 200px;
        position: absolute;
        bottom: -80px;
        left: 0;
        background-size: cover;
        border-radius: 20px;
        transition: all 300ms;
    }

    .cardd:nth-of-type(1) .img__back{
        background-image: url(https://www.wallpaperuse.com/wallp/43-435771_m.jpg);
    }

    .cardd:nth-of-type(2) .img__back{
        background-image: url(https://i.ibb.co/qRdhzd9/bg1-jpg.png);
    }

    .cardd:nth-of-type(3) .img__back{
        background-image: url(https://i.ibb.co/qRdhzd9/bg1-jpg.png);
    }

    .cardd:nth-of-type(4) .img__back{
        background-image: url(https://i.ibb.co/qRdhzd9/bg1-jpg.png);
    }

    .cardd:nth-of-type(5) .img__back{
        background-image: url(https://i.ibb.co/qRdhzd9/bg1-jpg.png);
    }

    .cardd:hover .img__back{
        bottom: -40px;
    }

    .cardd .description3 {
        /* background: white; */
        margin-top: -10px;
        /* padding: 20px; */
        border-radius: 0px 0px 20px 20px;
        transition: all 300ms;
    }


    .cardd:hover .description3{
        padding: 40px;
    }

    .cardd .description3 h2{
        margin-top: 10px;
    }

    .cardd .description3 p{
        margin-top: 10px;
    }

    .cardd .description3 input {
        padding: 0px 79px;
        margin-top: 8px;
        border: none;
        background: #0014e9;
        color: white;
        /* font-size: 20px; */
        cursor: pointer;
        border-radius: 15px;
        transition: all 300ms;
    }

    .cardd .description3 input:hover{
        background: #83277b;
    }

    .container-disney {
        margin: 0 auto;
        padding: 0 184px;
      }
      .main {
        margin: 0 auto;
        padding: 0 20px;
      }

      @media (min-width: 1300px){
        .disney-rows {
          max-width: 1600px;
          margin: 0 auto;
        }
      }
      .disney-rows {
          margin: 0 auto;
          padding: 0 19px;
      }

      .grid {
        display: grid;
        grid-row-gap: 14px;
        padding-bottom: 10px;
      }
      .card-disney {
        width: 100%;
        position: relative;
        cursor: pointer;
        border-radius: 10px;
      }

      .item-border {
        position: relative;
        border-radius: 10px;
        transition: transform 250ms ease-in-out, border 250ms ease-in-out,
          box-shadow 250ms ease-in-out;
      }
    .item-image {
        width: 100%;
        height: 100%;
        border-radius: 5px;
        z-index: 1;
      }
      .item-image.hover-image {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        transition-duration: 300ms;
        visibility: hidden;
      }
      .card-disney:hover .item-border {
        box-shadow: rgba(0, 0, 0, 0.8) 0px 40px 58px -16px,
          rgba(0, 0, 0, 0.72) 0px 30px 22px -10px;
        transform: scale(1.05);
        border: 4px solid rgba(249, 249, 249, 0.8);
      }

      .card-disney:hover .item-image.hover-image {
        position: absolute;
        top: 0;
        z-index: -1;
        opacity: 1;
        visibility: visible;
      }

      @media screen and (min-width: 300px) {
        .grid {
          grid-template-columns: 1fr 1fr 1fr 1fr 1fr ;
          grid-column-gap: 15px;
        }
        .films {
          grid-template-columns: 1fr 1fr ;
          grid-column-gap: 15px;
        }
        .item-border {
          border: 2px solid rgba(249, 249, 249, 0.1);
        }
      }
      @media screen and (min-width: 768px) {
        .grid {
          grid-template-columns: 1fr 1fr 1fr 1fr 1fr ;
          grid-column-gap: 5px;
        }
        .item-border {
          border: 4px solid rgba(249, 249, 249, 0.1);
        }
      }
      @media screen and (min-width: 1024px) {
        .grid {
          grid-template-columns: 1fr 1fr 1fr 1fr 1fr ;
          grid-column-gap: 39px;
        }
      }

    /*se divide aqui*/
    .slick-list {
      overflow: inherit;
    }
    .main {
      margin: 0 auto;
      padding: 0 20px;
    }
    @media (min-width: 1300px) {

    .container-general {
      margin-top: 20px;
      overflow: hidden;
    }
    @media (min-width: 1300px) {
      .movies-rows {
        max-width: 1600px;
        margin: 0 auto;
      }
    }
    .movies-rows {
      margin: 0 auto;
      padding: 0 20px;
    }

    .list-title {
      color: white;
      margin-top: 15px;
      margin-bottom: 15px;
      position: relative;
    }
    .slider h3 {
      border-radius: 5px;
      position: relative;
      text-align: center;
    }
    .slider div img {
      border-radius: 5px;
      width: 100%;
      height: auto;

    }
    .card {
      border-radius: 5px;
      display: flex !important;
      position: relative;
      box-shadow: rgb(0 0 0) 0px 16px 10px -10px;
      transition-duration: 300ms;
      transition-timing-function: ease-out;
    }
    .card::before {
      border-radius: 5px;
      border: 4px solid rgba(255, 255, 255, 0);
      inset: 0px;
      content: '';
      position: absolute;
      transition: border 300ms ease-out 0s;
      z-index: 1;
    }
    .card:hover::before {
      border: 4px solid rgba(249, 249, 249, 0.8);
    }
    .card:hover,
    .card img:hover {
      transform: scale(1.05, 1.05) translateZ(0px) translate3d(0px, 0px, 0px);
      transition-duration: 300ms;
      transition-property: transform, box-shadow;
      transition-timing-function: ease-out;
      box-shadow: rgb(0 0 0 / 90%) 0px 30px 20px -10px;
    }
  </style>

  <style id='tp_style_css' type='text/css'>
    /**************************/
    /*          General
        ***************************/
    /*(Main Color)*/
    a:hover,
    .SearchBtn > i,
    .Top:before,
    .TpMvPlay:before,
    .TPost.B .TPMvCn .TPlay:before,
    .SrtdBy li a:before,
    .Clra,
    .ShareList > li > a,
    .PlayMovie:hover,
    .VideoPlayer > span,
    .OptionBx p:before,
    .comment-reply-link:before,
    section > .Top > .Title > span,
    .widget_categories > ul li:hover > a:before,
    .Frm-Slct > label:before,
    .widget span.required,
    .comment-notes:before,
    .TPost .Description .CastList li:hover:before,
    .error-404:before,
    .widget_recent_comments li:before,
    .widget_recent_entries li:before,
    .widget_views li:before,
    .widget_rss li:before,
    .widget_meta li:before,
    .widget_pages li:before,
    .widget_archive li:before {
      color: #690dab;
    }
    .Tf-Wp.open .MenuBtn i,
    .owl-dots > div.active > span,
    #Tf-Wp.open .MenuBtn i,
    .TpTv,
    .TPost.C .Top,
    .TPost.C .Image,
    .Bgra,
    .VideoOptions.open + .BtnOptions,
    .lgtbx-on .VideoPlayer > span.BtnLight {
      background-color: #690dab;
    }
    .widget_nav_menu > div > ul > li[class*='current'],
    .widget_categories > ul > li:hover,
    .comment-list .children,
    blockquote {
      border-color: #690dab;
    }
    .menu-item-has-children > a:after,
    .SrtdBy:after {
      border-top-color: #690dab;
    }
    @media screen and (max-width: 62em) {
      .Menu {
        border-top-color: #690dab;
      }
    }
    @media screen and (min-width: 62em) {
      ::-webkit-scrollbar-thumb {
        background-color: #690dab;
      }
      .menu-item-has-children:hover > .sub-menu {
        border-top-color: #690dab;
      }
      .menu-item-has-children:after {
        border-bottom-color: #690dab;
      }
    }
    ::selection {
      background-color: #690dab;
      color: #fff;
    }
    ::-moz-selection {
      background-color: #690dab;
      color: #fff;
    }
    /*(Body Background)*/
    body {
      background-color: #1a191f;
    }
    /*(Text Color)*/
    body {
      color: #818083;
    }
    /*(Links Color)*/
    a,
    .ShareList.Count .numbr {
      color: #fff;
    }
    /*(Titles - Color)*/
    .Top > .Title,
    .Title.Top,
    .comment-reply-title,
    #email-notes,
    .Description h1,
    .Description h2,
    .Description h3,
    .Description h4,
    .Description h5,
    .Description h6,
    .Description legend {
      color: #fff;
    }
    /**************************/
    /*          Header
        ***************************/
    /*Background*/
    .Header:after {
      background-color: #000;
    }
    .BdGradient .Header:after {
      background: linear-gradient(to bottom, #000 0%, rgba(0, 0, 0, 0) 100%);
    }
    /*Menu*/
    /*(Menu Links Color)*/
    .Menu a,
    .SearchBtn {
      color: #fff;
    }
    .MenuBtn i {
      background-color: #fff;
    }
    /*(Menu Links Color Hover)*/
    .Menu li:hover a {
      color: #fff;
    }
    @media screen and (min-width: 62em) {
      .Menu [class*='current'] > a,
      .Header .Menu > ul > li:hover > a {
        color: #fff;
      }
    }
    /*(Menu Icons Color)*/
    .Menu li:before,
    .menu li:before {
      color: #690dab;
    }
    /*(Submenus Brackground)*/
    .Frm-Slct > label,
    .TPost.B .TPMvCn,
    .SrtdBy.open .List,
    .SearchMovies .sol-selection,
    .trsrcbx,
    .SearchMovies .sol-no-results,
    .OptionBx {
      background-color: #1a191f;
    }
    @media screen and (max-width: 62em) {
      .Menu {
        background-color: #1a191f;
      }
    }
    @media screen and (min-width: 62em) {
      .sub-menu {
        background-color: #1a191f;
      }
    }
    /*(Submenus Text Color)*/
    .Frm-Slct > label,
    .TPost.B .TPMvCn,
    .OptionBx {
      color: #818083;
    }
    /*(Submenus Links Color)*/
    .TPost.B .TPMvCn a,
    .OptionBx div,
    .sub-menu a,
    .Menu li:hover .sub-menu li > a {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu a {
        color: #fff;
      }
    }
    /*(Submenus Links Color Hover)*/
    .TPost.B .TPMvCn a:hover,
    .OptionBx a:hover,
    .sub-menu li:hover a,
    .Menu li:hover .sub-menu li:hover > a {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu li:hover a {
        color: #fff;
      }
    }
    /**************************/
    /*          Banner Top
        ***************************/
    /*(Banner Top Background)*/
    .TPost.A .Image:after,
    .TPost .Description .CastList:before {
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, #1a191f 100%);
    }
    /*(Banner Top Links Color)*/
    .MovieListSldCn .TPost.A .TPMvCn div a,
    .MovieListSldCn .TPost.A .TPMvCn .Title {
      color: #e0e0e0;
    }
    /*(Banner Top Links Color Hover)*/
    .MovieListSldCn .TPost.A .TPMvCn div a:hover,
    .MovieListSldCn .TPost.A .TPMvCn .Title:hover {
      color: #e0e0e0;
    }
    /*(Banner Top Text Color)*/
    .MovieListSldCn .TPost.A {
      color: #e0e0e0;
    }
    /**************************/
    /*          Forms
        ***************************/
    /*(Buttons Background)*/
    .Button,
    a.Button,
    a.Button:hover,
    button,
    input[type='button'],
    input[type='reset'],
    input[type='submit'],
    .BuyNow > a,
    .sol-selected-display-item,
    .trsrclst > li,
    .ShareList > li > a:hover,
    .TPost.B .Image .Qlty {
      background-color: #690dab;
    }
    .ShareList > li > a {
      border-color: #690dab;
    }
    /*(Buttons Background Hover)*/
    .Button:hover,
    .Button:hover,
    button:hover,
    input[type='button']:hover,
    input[type='reset']:hover,
    input[type='submit']:hover,
    .BuyNow > a:hover {
      background-color: #690dab;
    }
    /*(Buttons Text Color)*/
    .Button,
    a.Button,
    button,
    input[type='button'],
    input[type='reset'],
    input[type='submit'],
    .BuyNow > a,
    .sol-selected-display-item,
    .trsrclst > li,
    .ShareList > li > a:hover,
    .TPost.B .Image .Qlty {
      color: #fff;
    }
    /*(Buttons Text Color Hover)*/
    .Button:hover,
    .Button:hover,
    button:hover,
    input[type='button']:hover,
    input[type='reset']:hover,
    input[type='submit']:hover,
    .BuyNow > a:hover {
      color: #fff;
    }
    /*(Form controls Background)*/
    input,
    textarea,
    select,
    .Form-Select label,
    .OptionBx p {
      background-color: #2a292f;
    }
    /*(Form controls Text Color)*/
    input,
    textarea,
    select,
    .Form-Select label,
    .OptionBx p {
      color: #fff;
    }
    /**************************/
    /*          Widgets
        ***************************/
    /*(Widget - Backgorund)*/
    aside .Wdgt {
      background-color: #212026;
    }
    /*(Widget Title - Backgorund)*/
    aside .Wdgt > .Title {
      background-color: #19181d;
    }
    /*(Widget Title - Color)*/
    aside .Wdgt > .Title {
      color: #fff;
    }
    /*(Widget Text Color)*/
    aside .Wdgt {
      color: #818083;
    }
    /*(Widget Links Color)*/
    aside .Wdgt a {
      color: #fff;
    }
    /*(Widget Links Color Hover)*/
    aside .Wdgt a:hover {
      color: #690dab;
    }
    /**************************/
    /*          Tables
        ***************************/
    /*(Table Title Background)*/
    thead tr {
      background-color: #690dab;
    }
    /*(Table Title Text)*/
    thead tr {
      color: #fff;
    }
    /*(Table Cell Background)*/
    td {
      background-color: #26252a;
    }
    .SeasonBx {
      border-bottom-color: #26252a;
    }
    /*(Table Cell Background Hover )*/
    tr:hover > td,
    tr.Viewed td {
      background-color: #313036;
    }
    /*(Table Cell Text)*/
    td {
      color: #818083;
    }
    /*(Table Cell Links)*/
    td a,
    .TPTblCnMvs td:first-child,
    .TPTblCnMvs td:nth-child(2),
    .TPTblCnMvs td:nth-child(3) {
      color: #fff;
    }
    /*(Table Cell Links Hover)*/
    td a:hover {
      color: #690dab;
    }
    /**************************/
    /*          Pagination
        ***************************/
    /*Pagination Links Background*/
    .menu-azlist ul.sub-menu a,
    .AZList > li > a,
    .wp-pagenavi a,
    .wp-pagenavi span,
    .nav-links a,
    .nav-links span,
    .tagcloud a {
      background-color: #313036;
    }
    @media screen and (max-width: 62em) {
      .Menu > ul > li {
        border-bottom-color: #313036;
      }
      .Menu .sub-menu a {
        background-color: #313036;
      }
    }
    /*Pagination Links Background Hover*/
    .menu-azlist ul.sub-menu a:hover,
    .menu-azlist [class*='current'] > a,
    .AZList a:hover,
    .AZList .Current a,
    .wp-pagenavi a:hover,
    .wp-pagenavi span.current,
    .nav-links a:hover,
    .nav-links [class*='current'],
    .tagcloud a:hover {
      background-color: #690dab;
    }
    @media screen and (max-width: 62em) {
      .Menu .sub-menu a:hover {
        background-color: #690dab;
      }
    }
    /*Pagination Links Color*/
    .menu-azlist ul.sub-menu a,
    .AZList > li > a,
    .wp-pagenavi a,
    .wp-pagenavi span,
    .tagcloud a {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu .sub-menu a {
        color: #fff !important;
      }
    }
    /*Pagination Links Color Hover*/
    .Menu li.menu-azlist:hover ul.sub-menu a:hover,
    .menu-azlist [class*='current'] > a,
    .AZList a:hover,
    .AZList .Current a,
    .wp-pagenavi a:hover,
    .wp-pagenavi span.current,
    .nav-links a:hover,
    .nav-links [class*='current'],
    .tagcloud a:hover {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu li:hover .sub-menu li:hover a,
      .Menu .sub-menu li:hover:before {
        color: #fff !important;
      }
    }
    /**************************/
    /*          Footer
        ***************************/
    /*Top*/
    /*(Footer Top - Background)*/
    .Footer .Top {
      background-color: #151419;
    }
    /*(Footer Top - Text Color)*/
    .Footer .Top {
      color: #818083;
    }
    /*(Footer Top - Links Color)*/
    .Footer .Top a {
      color: #fff;
    }
    /*(Footer Top - Links Color Hover)*/
    .Footer .Top a:hover {
      color: #690dab;
    }
    /*Bot*/
    /*(Footer Bot - Background)*/
    .Footer .Bot {
      background-color: #1a191f;
    }
    /*(Footer Bot - Text Color)*/
    .Footer .Bot {
      color: #818083;
    }
    /*(Footer Bot - Links Color)*/
    .Footer .Bot a {
      color: #fff;
    }
    /*(Footer Bot - Links Color Hover)*/
    .Footer .Bot a:hover {
      color: #690dab;
    }
    /****************************  NO EDIT  ****************************/
    .Search input[type='text'] {
      background-color: rgba(255, 255, 255, 0.2);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
      color: #fff;
    }
    .Search input[type='text']:focus {
      background-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.5),
        inset 0 0 0 1px rgba(255, 255, 255, 0.2);
    }
    .Button,
    a.Button,
    button,
    input[type='button'],
    input[type='reset'],
    input[type='submit'],
    .BuyNow > a,
    .wp-pagenavi .current,
    thead tr,
    .nav-links [class*='current'] {
      box-shadow: inset 0 -10px 20px rgba(0, 0, 0, 0.3);
    }
    .Button:hover,
    .Button:hover,
    button:hover,
    input[type='button']:hover,
    input[type='reset']:hover,
    input[type='submit']:hover,
    .BuyNow > a:hover {
      box-shadow: none;
    }
    .TPost.B .TPMvCn,
    aside .Wdgt,
    .SrtdBy.open .List,
    .sol-active.sol-selection-top .sol-selection-container,
    .trsrcbx,
    .sub-menu,
    .OptionBx,
    .wp-pagenavi a,
    .wp-pagenavi span,
    .nav-links a,
    .nav-links span,
    .tagcloud a {
      box-shadow: inset 0 0 70px rgba(0, 0, 0, 0.3), 0 0 20px rgba(0, 0, 0, 0.5);
    }
    .widget_categories > ul li:hover,
    .sol-option:hover {
      box-shadow: inset 0 0 70px rgba(0, 0, 0, 0.2);
    }
    @media screen and (max-width: 62em) {
      .sub-menu {
        box-shadow: none;
      }
    }
  </style>

  <link
    rel='stylesheet'
    id='TOROFLIX_Theme-css'
    href='http://haytex.epizy.com/css/public.css?ver=1.2.0'
    type='text/css'
    media='all'
  />
  <script
    type='text/javascript'
    src='http://haytex.epizy.com/js/jquery.min.js?ver=3.6.1'
    id='jquery-core-js'
  ></script>

  <link
    rel='icon'
    href='http://haytex.epizy.com/Haytex_logo.png'
    sizes='192x192'
  />
  <style type='text/css' id='wp-custom-css'>
    			.TPost .Image figure {
        position: relative;
        padding-top: 150%;
        border-radius: 10px;
    }

    .Header .Logo {
        padding: 5px;
        width: 180px;
        text-align: center;
    }

    aside .widget_categories ul {
        max-height: 400px;
    }


    .VideoPlayer>span[class*='Btn'] {
        right: -25px;
        display: none !important;
    }

    #admsg {
        background: #b10000;
        color: white !important;
        padding: 10px;
        border-radius: 6px;
        text-align: center;
        font-size: 17px;
    }

    .Button.Sm {
        padding: 0 .5rem;
        border-radius: 5px;
        line-height: 1.35rem;
        font-size: .80rem;
    }


    a#\33 456258 {
        background: #760036;
        padding: 10px;
        color: white;
        border-radius: 8px;
        bottom: 5px !important;
        position: relative;
    }
    #ancr-22704 .ancr-content a {
        color: #fff!important;
        font-family: roboto!important;
        font-size: 15px!important;
        font-weight: 600!important;
        color: #006cff !important;
    }

    tr:hover>td, tr.Viewed td {
        background-color: #212026 !Important;
    }

    .TPTblCn td.MvTbImg.B img:hover{
    	opacity: .8;
    }

    .Cast .view-sh {
        font-size: 10px;
        text-transform: uppercase;
        padding: 0 15px;
        line-height: 23px;
        border-radius: 8px;
        height: auto;
        margin-left: 5px;
    }


    .ShareList>li>a:hover{
    	cursor: pointer;
    }

    .Qlty {
        color: #690dab;
    }


    .Image:hover
    {
    	opacity: .4;
      transition: 0.4s;
    }

    label[for] {
        cursor: pointer;
        color: red;
    }

    section>.Top>.Title, article>.Top>.Title {
        font-weight: 700;
        font-size: 1.125rem;
        margin-bottom: 0;
        padding: 5px;
        display: inline-block;
        vertical-align: top;
        margin-right: .5rem;
        background: #212026;
        width: auto;
        border-radius: 5px;
        box-shadow: inset 0 0 10px rgb(0 0 0 / 40%), 0 0 20px rgb(0 0 0 / 50%);
        padding-right: 10px;
    }

    .Qlty {
        border-radius: 5px;
    }

    .SrtdBy>i {
        width: 120px;
        box-shadow: inset 0 0 10px rgb(0 0 0 / 40%), 0 0 20px rgb(0 0 0 / 50%);
        padding-right: 10px;
    }



    section>.Top[class*='fa-']:before, section>.Top[class*='AAIco-']:before, article>.Top[class*='fa-']:before, article>.Top[class*='AAIco-']:before {
        position: absolute;
        font-size: 1.5625rem;
        width: 1.5625rem;
        height: 1.5625rem;
        left: 5px;
        top: 5px;
    }

    @media (max-width: 400px) {
        .Header .Logo  {
        padding: 50px;
        width: 250px;
    		bottom: 70px;
    		left: 50px;
        text-align: center;
    	position: relative;
    	}
    	.ads-bottom-title {
        display: flex;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        flex-wrap: wrap;
        justify-content: center;
        -ms-flex-pack: center;
        -webkit-box-pack: center;
        -webkit-justify-content: center;
        margin-bottom: 30px;
        width: 80%;
        margin-top: 30px;
    }

    .OptionBx .Optntl {
        position: relative;
        font-weight: 700;
        display: none;
        letter-spacing: -1px;
        font-size: 1.25rem;
        line-height: 3.125rem;
        margin-bottom: .5rem;
    }

    body {
        color: gainsboro;
    }

    .menu-item-has-children:hover>.sub-menu {
        border-top-color: #690dab;
        max-height: 900px !important;
        border-radius: 10px !important;
    }

    	.VideoPlayer .Video {
        position: relative;
        min-height: 250px;
        border-radius: 15px !important;
        max-height: 500px;
        overflow: hidden;
        display: none;
        animation: scale 0.7s ease-in-out;
    }

    .TPost .Image figure img {
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 10px !important;
        width: 100%;
        height: 100%;
    }


    	article, aside, details, figcaption, figure, footer, header, main, menu, nav, section, summary, picture {
        display: block;
        border-radius: 10px !important;
    }

    	.Button, a.Button, button, input[type='button'], input[type='reset'], input[type='submit'], .BuyNow>a, .wp-pagenavi .current, thead tr, .nav-links [class*='current'] {
        border-radius: 6px !important;
    }

    	.Button.Sm {
        padding: 0 .5rem;
        border-radius: 6px !important;
        line-height: 1.25rem;
        font-size: .80rem;
    }

    	.Button, button, input[type='button'], input[type='reset'], input[type='submit'] {
        border: 0;
        cursor: pointer;
        padding: 5px 1rem;
        width: auto;
        display: inline-block;
        text-align: center;
        line-height: 1.875rem;
        border-radius: 4px !important;
    }


    	.VideoPlayer>span.BtnOptions {
        top: -70px;
        white-space: nowrap;
        overflow: hidden;
        display: none !important;
    }
  </style>

  <style id='tp_style_css' type='text/css'>
    /**************************/
    /*          General
        ***************************/
    /*(Main Color)*/
    a:hover,
    .SearchBtn > i,
    .Top:before,
    .TpMvPlay:before,
    .TPost.B .TPMvCn .TPlay:before,
    .SrtdBy li a:before,
    .Clra,
    .ShareList > li > a,
    .PlayMovie:hover,
    .VideoPlayer > span,
    .OptionBx p:before,
    .comment-reply-link:before,
    section > .Top > .Title > span,
    .widget_categories > ul li:hover > a:before,
    .Frm-Slct > label:before,
    .widget span.required,
    .comment-notes:before,
    .TPost .Description .CastList li:hover:before,
    .error-404:before,
    .widget_recent_comments li:before,
    .widget_recent_entries li:before,
    .widget_views li:before,
    .widget_rss li:before,
    .widget_meta li:before,
    .widget_pages li:before,
    .widget_archive li:before {
      color: #690dab;
    }

    .widget_nav_menu > div > ul > li[class*='current'],
    .widget_categories > ul > li:hover,
    .comment-list .children,
    blockquote {
      border-color: #690dab;
    }
    .menu-item-has-children > a:after,
    .SrtdBy:after {
      border-top-color: #690dab;
    }
    @media screen and (max-width: 62em) {
      .Menu {
        border-top-color: #690dab;
      }
    }
    @media screen and (min-width: 62em) {
      ::-webkit-scrollbar-thumb {
        background-color: #690dab;
      }
      .menu-item-has-children:hover > .sub-menu {
        border-top-color: #690dab;
      }
      .menu-item-has-children:after {
        border-bottom-color: #690dab;
      }
    }
    ::selection {
      background-color: #690dab;
      color: #fff;
    }
    ::-moz-selection {
      background-color: #690dab;
      color: #fff;
    }
    /*(Body Background)*/
    body {
      background-color: #1a191f;
    }
    /*(Text Color)*/
    body {
      color: #d1caca;
    }
    /*(Links Color)*/
    a,
    .ShareList.Count .numbr {
      color: #ffffff;
    }
    /*(Titles - Color)*/
    .Top > .Title,
    .Title.Top,
    .comment-reply-title,
    #email-notes,
    .Description h1,
    .Description h2,
    .Description h3,
    .Description h4,
    .Description h5,
    .Description h6,
    .Description legend {
      color: #fff;
    }
    /**************************/
    /*          Header
        ***************************/
    /*Background*/
    .Header:after {
      background-color: #000;
    }
    .BdGradient .Header:after {
      background: linear-gradient(to bottom, #000 0%, rgba(0, 0, 0, 0) 100%);
    }
    /*Menu*/
    /*(Menu Links Color)*/
    .Menu a,
    .SearchBtn {
      color: #fff;
    }
    .MenuBtn i {
      background-color: #fff;
    }
    /*(Menu Links Color Hover)*/
    .Menu li:hover a {
      color: #690dab;
    }
    @media screen and (min-width: 62em) {
      .Menu [class*='current'] > a,
      .Header .Menu > ul > li:hover > a {
        color: #690dab;
      }
    }
    /*(Menu Icons Color)*/
    .Menu li:before,
    .menu li:before {
      color: #690dab;
    }
    /*(Submenus Brackground)*/
    .Frm-Slct > label,
    .TPost.B .TPMvCn,
    .SrtdBy.open .List,
    .SearchMovies .sol-selection,
    .trsrcbx,
    .SearchMovies .sol-no-results,
    .OptionBx {
      background-color: #1a191f;
    }
    @media screen and (max-width: 62em) {
      .Menu {
        background-color: #1a191f;
      }
    }
    @media screen and (min-width: 62em) {
      .sub-menu {
        background-color: #1a191f;
      }
    }
    /*(Submenus Text Color)*/
    .Frm-Slct > label,
    .TPost.B .TPMvCn,
    .OptionBx {
      color: #818083;
    }
    /*(Submenus Links Color)*/
    .TPost.B .TPMvCn a,
    .OptionBx div,
    .sub-menu a,
    .Menu li:hover .sub-menu li > a {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu a {
        color: #fff;
      }
    }
    /*(Submenus Links Color Hover)*/
    .TPost.B .TPMvCn a:hover,
    .OptionBx a:hover,
    .sub-menu li:hover a,
    .Menu li:hover .sub-menu li:hover > a {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu li:hover a {
        color: #fff;
      }
    }
    /**************************/
    /*          Banner Top
        ***************************/
    /*(Banner Top Background)*/
    .TPost.A .Image:after,
    .TPost .Description .CastList:before {
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, #1a191f 100%);
    }
    /*(Banner Top Links Color)*/
    .MovieListSldCn .TPost.A .TPMvCn div a,
    .MovieListSldCn .TPost.A .TPMvCn .Title {
      color: #e0e0e0;
    }
    /*(Banner Top Links Color Hover)*/
    .MovieListSldCn .TPost.A .TPMvCn div a:hover,
    .MovieListSldCn .TPost.A .TPMvCn .Title:hover {
      color: #e0e0e0;
    }
    /*(Banner Top Text Color)*/
    .MovieListSldCn .TPost.A {
      color: #e0e0e0;
    }
    /**************************/
    /*          Forms
        ***************************/
    /*(Buttons Background)*/
    .Button,
    a.Button,
    a.Button:hover,
    button,
    input[type='button'],
    input[type='reset'],
    input[type='submit'],
    .BuyNow > a,
    .sol-selected-display-item,
    .trsrclst > li,
    .ShareList > li > a:hover,
    .TPost.B .Image .Qlty {
      background-color: #690dab;
    }
    .ShareList > li > a {
      border-color: #690dab;
    }
    /*(Buttons Background Hover)*/
    .Button:hover,
    .Button:hover,
    button:hover,
    input[type='button']:hover,
    input[type='reset']:hover,
    input[type='submit']:hover,
    .BuyNow > a:hover {
      background-color: #690dab;
    }
    /*(Buttons Text Color)*/
    .Button,
    a.Button,
    button,
    input[type='button'],
    input[type='reset'],
    input[type='submit'],
    .BuyNow > a,
    .sol-selected-display-item,
    .trsrclst > li,
    .ShareList > li > a:hover,
    .TPost.B .Image .Qlty {
      color: #fff;
    }
    /*(Buttons Text Color Hover)*/
    .Button:hover,
    .Button:hover,
    button:hover,
    input[type='button']:hover,
    input[type='reset']:hover,
    input[type='submit']:hover,
    .BuyNow > a:hover {
      color: #c6c6c6;
    }
    /*(Form controls Background)*/
    input,
    textarea,
    select,
    .Form-Select label,
    .OptionBx p {
      background-color: #2a292f;
    }
    /*(Form controls Text Color)*/
    input,
    textarea,
    select,
    .Form-Select label,
    .OptionBx p {
      color: #fff;
    }
    /**************************/
    /*          Widgets
        ***************************/
    /*(Widget - Backgorund)*/
    aside .Wdgt {
      background-color: #212026;
    }
    /*(Widget Title - Backgorund)*/
    aside .Wdgt > .Title {
      background-color: #19181d;
    }
    /*(Widget Title - Color)*/
    aside .Wdgt > .Title {
      color: #fff;
    }
    /*(Widget Text Color)*/
    aside .Wdgt {
      color: #818083;
    }
    /*(Widget Links Color)*/
    aside .Wdgt a {
      color: #fff;
    }
    /*(Widget Links Color Hover)*/
    aside .Wdgt a:hover {
      color: #690dab;
    }
    /**************************/
    /*          Tables
        ***************************/
    /*(Table Title Background)*/
    thead tr {
      background-color: #690dab;
    }
    /*(Table Title Text)*/
    thead tr {
      color: #fff;
    }
    /*(Table Cell Background)*/
    td {
      background-color: #26252a;
    }
    .SeasonBx {
      border-bottom-color: #26252a;
    }
    /*(Table Cell Background Hover )*/
    tr:hover > td,
    tr.Viewed td {
      background-color: #313036;
    }
    /*(Table Cell Text)*/
    td {
      color: #818083;
    }
    /*(Table Cell Links)*/
    td a,
    .TPTblCnMvs td:first-child,
    .TPTblCnMvs td:nth-child(2),
    .TPTblCnMvs td:nth-child(3) {
      color: #fff;
    }
    /*(Table Cell Links Hover)*/
    td a:hover {
      color: #690dab;
    }
    /**************************/
    /*          Pagination
        ***************************/
    /*Pagination Links Background*/
    .menu-azlist ul.sub-menu a,
    .AZList > li > a,
    .wp-pagenavi a,
    .wp-pagenavi span,
    .nav-links a,
    .nav-links span,
    .tagcloud a {
      background-color: #313036;
    }
    @media screen and (max-width: 62em) {
      .Menu > ul > li {
        border-bottom-color: #313036;
      }
      .Menu .sub-menu a {
        background-color: #313036;
      }
    }
    /*Pagination Links Background Hover*/
    .menu-azlist ul.sub-menu a:hover,
    .menu-azlist [class*='current'] > a,
    .AZList a:hover,
    .AZList .Current a,
    .wp-pagenavi a:hover,
    .wp-pagenavi span.current,
    .nav-links a:hover,
    .nav-links [class*='current'],
    .tagcloud a:hover {
      background-color: #690dab;
    }
    @media screen and (max-width: 62em) {
      .Menu .sub-menu a:hover {
        background-color: #690dab;
      }
    }
    /*Pagination Links Color*/
    .menu-azlist ul.sub-menu a,
    .AZList > li > a,
    .wp-pagenavi a,
    .wp-pagenavi span,
    .tagcloud a {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu .sub-menu a {
        color: #fff !important;
      }
    }
    /*Pagination Links Color Hover*/
    .Menu li.menu-azlist:hover ul.sub-menu a:hover,
    .menu-azlist [class*='current'] > a,
    .AZList a:hover,
    .AZList .Current a,
    .wp-pagenavi a:hover,
    .wp-pagenavi span.current,
    .nav-links a:hover,
    .nav-links [class*='current'],
    .tagcloud a:hover {
      color: #fff !important;
    }
    @media screen and (max-width: 62em) {
      .Menu li:hover .sub-menu li:hover a,
      .Menu .sub-menu li:hover:before {
        color: #fff !important;
      }
    }
    /**************************/
    /*          Footer
        ***************************/
    /*Top*/
    /*(Footer Top - Background)*/
    .Footer .Top {
      background-color: #151419;
    }
    /*(Footer Top - Text Color)*/
    .Footer .Top {
      color: #818083;
    }
    /*(Footer Top - Links Color)*/
    .Footer .Top a {
      color: #fff;
    }
    /*(Footer Top - Links Color Hover)*/
    .Footer .Top a:hover {
      color: #690dab;
    }
    /*Bot*/
    /*(Footer Bot - Background)*/
    .Footer .Bot {
      background-color: #1a191f;
    }
    /*(Footer Bot - Text Color)*/
    .Footer .Bot {
      color: #818083;
    }
    /*(Footer Bot - Links Color)*/
    .Footer .Bot a {
      color: #fff;
    }
    /*(Footer Bot - Links Color Hover)*/
    .Footer .Bot a:hover {
      color: #690dab;
    }
    /****************************  NO EDIT  ****************************/
    .Search input[type='text'] {
      background-color: rgba(255, 255, 255, 0.2);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
      color: #fff;
    }
    .Search input[type='text']:focus {
      background-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.5),
        inset 0 0 0 1px rgba(255, 255, 255, 0.2);
    }
    .Button,
    a.Button,
    button,
    input[type='button'],
    input[type='reset'],
    input[type='submit'],
    .BuyNow > a,
    .wp-pagenavi .current,
    thead tr,
    .nav-links [class*='current'] {
      box-shadow: inset 0 -10px 20px rgba(0, 0, 0, 0.3);
    }
    .Button:hover,
    .Button:hover,
    button:hover,
    input[type='button']:hover,
    input[type='reset']:hover,
    input[type='submit']:hover,
    .BuyNow > a:hover {
      box-shadow: none;
    }
    .TPost.B .TPMvCn,
    aside .Wdgt,
    .SrtdBy.open .List,
    .sol-active.sol-selection-top .sol-selection-container,
    .trsrcbx,
    .sub-menu,
    .OptionBx,
    .wp-pagenavi a,
    .wp-pagenavi span,
    .nav-links a,
    .nav-links span,
    .tagcloud a {
      box-shadow: inset 0 0 70px rgba(0, 0, 0, 0.3), 0 0 20px rgba(0, 0, 0, 0.5);
    }
    .widget_categories > ul li:hover,
    .sol-option:hover {
      box-shadow: inset 0 0 70px rgba(0, 0, 0, 0.2);
    }
    @media screen and (max-width: 62em) {
      .sub-menu {
        box-shadow: none;
      }
    }
    header #user{float:right;margin-top:1px}header #user .guest{cursor:pointer;opacity:.8;-webkit-transition:all .2s ease-in-out 0s;-moz-transition:all .2s ease-in-out 0s;transition:all .2s ease-in-out 0s;margin-top:3px}header #user .guest:hover{opacity:1}header #user .guest i{font-size:2em;vertical-align:-6px;margin-top:5px;margin-right:6px}header #user .logged{background:#690dab;color:#e9ecef;line-height:40px;height:40px;border-radius:20px;padding:0 15px;cursor:pointer;-webkit-transition:all .2s ease-in-out 0s;-moz-transition:all .2s ease-in-out 0s;transition:all .2s ease-in-out 0s;font-size:.9em;font-weight:500;margin-top:4px}header #user .logged:hover{box-shadow:0 3px 11px rgba(17,17,17,.5);background:#007f8e}header #user .logged s,header #user .logged:after{display:none}header #user .logged i.fa-sort-down{vertical-align:3px}@media screen and (max-width:1599px){header #menu>li>a{margin:0 7px}header #search{margin-left:1.8em;width:340px}}@media screen and (max-width:1365px){header #menu{margin-left:25px}header #menu>li>a{margin:0 5px}header #search{width:300px}}@media screen and (max-width:1279px){header{padding-top:15px}header #menu-toggler{display:inline-block;float:left;margin-right:15px;margin-top:2px;font-size:2em;opacity:.8}header #menu{display:none;background:#212529;position:absolute;margin:0;top:60px;left:10px;right:10px;border-radius:.3rem;overflow:hidden;max-width:360px;padding:10px;border-radius:5px;box-shadow:0 0 11px #111}header #menu>li{display:block;width:100%}header #menu>li+li>a{border-top:1px solid #1a1d21}header #menu>li>a{padding:10px 0;display:block}header #menu>li>a>i{display:block;float:right;margin-top:5px;background:#dee2e6;color:#212529;padding:2px 3px;border-radius:2px;font-size:.7em}header #menu>li>ul{position:static;width:100%!important;overflow:hidden;box-shadow:none;background:#16181b}header #menu>li>ul>li{width:33.33%!important}header #menu>li>ul.country>li{width:50%!important}header #menu>li:hover>ul{display:none}header #search{float:right;margin-right:2em;width:380px}}@media screen and (max-width:768px){header #search-toggler{display:block;opacity:.8;float:right;font-size:2em;margin-right:20px}header #search{display:none;margin:0;width:100%}header #user{margin-top:-1px}header #user .guest span{display:none}}@media screen and (max-width:576px){header #menu>li>ul>li{width:50%!important}header #user{margin-top:0}header #user .guest span{display:none}header #user .logged{height:30px;line-height:30px;width:30px;text-align:center;padding:0;font-size:1.2em;margin-top:5px}header #user .logged span{display:none}}@media screen and (max-width:320px){header #menu>li>ul{font-size:.9em;padding:5px 0}}#slider{height:52em;width:100%;position:relative;margin-bottom:30px;overflow:hidden}#slider .item{width:100%;height:100%;background-repeat:no-repeat;background-size:cover;background-position:center top;position:relative}#slider .item .backdrop{position:absolute;top:0;left:0;width:100%;height:100%;overflow:hidden}#slider .item .backdrop img{width:100%}#slider .item:before{position:absolute;z-index:1;content:'';bottom:0;top:0;width:100%;background:-moz-radial-gradient(center,ellipse cover,transparent 10%,#000 100%);background:-webkit-gradient(radial,center center,0,center center,100%,color-stop(10%,transparent),color-stop(100%,#000));background:-webkit-radial-gradient(center,ellipse cover,transparent 10%,#000 100%);background:-o-radial-gradient(center,ellipse cover,transparent 10%,#000 100%);background:-ms-radial-gradient(center,ellipse cover,transparent 10%,#000 100%);background:radial-gradient(ellipse at center,transparent 10%,#000 100%)}#slider .item:after{position:absolute;z-index:1;content:'';bottom:0;width:100%;height:150px;background-color:rgba(17,17,17,0);background-image:-webkit-gradient(linear,left top,left bottom,from(rgba(17,17,17,0)),to(#111));background-image:-webkit-linear-gradient(top,rgba(17,17,17,0),#111);background-image:-moz-linear-gradient(top,rgba(17,17,17,0),#111);background-image:-ms-linear-gradient(top,rgba(17,17,17,0),#111);background-image:-o-linear-gradient(top,rgba(17,17,17,0),#111);background-image:linear-gradient(top,rgba(17,17,17,0),#111)}#slider .item .info{position:absolute;z-index:2;bottom:5em;max-width:650px;color:#fff}#slider .item .info .title{font-size:3em;font-weight:500;margin:0;margin-bottom:.2em;text-shadow:0 1px 10px #111}#slider .item .info .meta{margin-bottom:1.2em}.btn:hover{color:#bbb;text-decoration:none}.btn.focus,.btn:focus{outline:0;box-shadow:0 0 0 .2rem rgba(52,58,64,.25)}.btn.disabled,.btn:disabled{opacity:.65}.btn:not(:disabled):not(.disabled){cursor:pointer}a.btn.disabled,fieldset:disabled a.btn{pointer-events:none}.btn-primary{color:#fff;background-color:#00acc1;border-color:#00acc1}.btn-primary:hover{color:#fff;background-color:#008a9b;border-color:#007f8e}.btn-primary.focus,.btn-primary:focus{color:#fff;background-color:#008a9b;border-color:#007f8e;box-shadow:0 0 0 .2rem rgba(38,184,202,.5)}.btn-primary.disabled,.btn-primary:disabled{color:#fff;background-color:#00acc1;border-color:#00acc1}.btn-primary:not(:disabled):not(.disabled).active,.btn-primary:not(:disabled):not(.disabled):active,.show>.btn-primary.dropdown-toggle{color:#fff;background-color:#007f8e;border-color:#007381}.btn-primary:not(:disabled):not(.disabled).active:focus,.btn-primary:not(:disabled):not(.disabled):active:focus,.show>.btn-primary.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(38,184,202,.5)}.btn-secondary{color:#fff;background-color:#212529;border-color:#212529}.btn-secondary:hover{color:#fff;background-color:#101214;border-color:#0a0c0d}.btn-secondary.focus,.btn-secondary:focus{color:#fff;background-color:#101214;border-color:#0a0c0d;box-shadow:0 0 0 .2rem rgba(66,70,73,.5)}.btn-secondary.disabled,.btn-secondary:disabled{color:#fff;background-color:#212529;border-color:#212529}.btn-secondary:not(:disabled):not(.disabled).active,.btn-secondary:not(:disabled):not(.disabled):active,.show>.btn-secondary.dropdown-toggle{color:#fff;background-color:#0a0c0d;border-color:#050506}.btn-secondary:not(:disabled):not(.disabled).active:focus,.btn-secondary:not(:disabled):not(.disabled):active:focus,.show>.btn-secondary.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(66,70,73,.5)}.btn-success{color:#fff;background-color:#28a745;border-color:#28a745}.btn-success:hover{color:#fff;background-color:#218838;border-color:#1e7e34}.btn-success.focus,.btn-success:focus{color:#fff;background-color:#218838;border-color:#1e7e34;box-shadow:0 0 0 .2rem rgba(72,180,97,.5)}.btn-success.disabled,.btn-success:disabled{color:#fff;background-color:#28a745;border-color:#28a745}.btn-success:not(:disabled):not(.disabled).active,.btn-success:not(:disabled):not(.disabled):active,.show>.btn-success.dropdown-toggle{color:#fff;background-color:#1e7e34;border-color:#1c7430}.btn-success:not(:disabled):not(.disabled).active:focus,.btn-success:not(:disabled):not(.disabled):active:focus,.show>.btn-success.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(72,180,97,.5)}.btn-info{color:#fff;background-color:#17a2b8;border-color:#17a2b8}.btn-info:hover{color:#fff;background-color:#138496;border-color:#117a8b}.btn-info.focus,.btn-info:focus{color:#fff;background-color:#138496;border-color:#117a8b;box-shadow:0 0 0 .2rem rgba(58,176,195,.5)}.btn-info.disabled,.btn-info:disabled{color:#fff;background-color:#17a2b8;border-color:#17a2b8}.btn-info:not(:disabled):not(.disabled).active,.btn-info:not(:disabled):not(.disabled):active,.show>.btn-info.dropdown-toggle{color:#fff;background-color:#117a8b;border-color:#10707f}.btn-info:not(:disabled):not(.disabled).active:focus,.btn-info:not(:disabled):not(.disabled):active:focus,.show>.btn-info.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(58,176,195,.5)}.btn-warning{color:#212529;background-color:#ffc107;border-color:#ffc107}.btn-warning:hover{color:#212529;background-color:#e0a800;border-color:#d39e00}.btn-warning.focus,.btn-warning:focus{color:#212529;background-color:#e0a800;border-color:#d39e00;box-shadow:0 0 0 .2rem rgba(222,170,12,.5)}.btn-warning.disabled,.btn-warning:disabled{color:#212529;background-color:#ffc107;border-color:#ffc107}.btn-warning:not(:disabled):not(.disabled).active,.btn-warning:not(:disabled):not(.disabled):active,.show>.btn-warning.dropdown-toggle{color:#212529;background-color:#d39e00;border-color:#c69500}.btn-warning:not(:disabled):not(.disabled).active:focus,.btn-warning:not(:disabled):not(.disabled):active:focus,.show>.btn-warning.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(222,170,12,.5)}.btn-danger{color:#fff;background-color:#dc3545;border-color:#dc3545}.btn-danger:hover{color:#fff;background-color:#c82333;border-color:#bd2130}.btn-danger.focus,.btn-danger:focus{color:#fff;background-color:#c82333;border-color:#bd2130;box-shadow:0 0 0 .2rem rgba(225,83,97,.5)}.btn-danger.disabled,.btn-danger:disabled{color:#fff;background-color:#dc3545;border-color:#dc3545}.btn-danger:not(:disabled):not(.disabled).active,.btn-danger:not(:disabled):not(.disabled):active,.show>.btn-danger.dropdown-toggle{color:#fff;background-color:#bd2130;border-color:#b21f2d}.btn-danger:not(:disabled):not(.disabled).active:focus,.btn-danger:not(:disabled):not(.disabled):active:focus,.show>.btn-danger.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(225,83,97,.5)}.btn-light{color:#212529;background-color:#f8f9fa;border-color:#f8f9fa}.btn-light:hover{color:#212529;background-color:#e2e6ea;border-color:#dae0e5}.btn-light.focus,.btn-light:focus{color:#212529;background-color:#e2e6ea;border-color:#dae0e5;box-shadow:0 0 0 .2rem rgba(216,217,219,.5)}.btn-light.disabled,.btn-light:disabled{color:#212529;background-color:#f8f9fa;border-color:#f8f9fa}.btn-light:not(:disabled):not(.disabled).active,.btn-light:not(:disabled):not(.disabled):active,.show>.btn-light.dropdown-toggle{color:#212529;background-color:#dae0e5;border-color:#d3d9df}.btn-light:not(:disabled):not(.disabled).active:focus,.btn-light:not(:disabled):not(.disabled):active:focus,.show>.btn-light.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(216,217,219,.5)}.btn-dark{color:#fff;background-color:#343a40;border-color:#343a40}.btn-dark:hover{color:#fff;background-color:#23272b;border-color:#1d2124}.btn-dark.focus,.btn-dark:focus{color:#fff;background-color:#23272b;border-color:#1d2124;box-shadow:0 0 0 .2rem rgba(82,88,93,.5)}.btn-dark.disabled,.btn-dark:disabled{color:#fff;background-color:#343a40;border-color:#343a40}.btn-dark:not(:disabled):not(.disabled).active,.btn-dark:not(:disabled):not(.disabled):active,.show>.btn-dark.dropdown-toggle{color:#fff;background-color:#1d2124;border-color:#171a1d}.btn-dark:not(:disabled):not(.disabled).active:focus,.btn-dark:not(:disabled):not(.disabled):active:focus,.show>.btn-dark.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(82,88,93,.5)}.btn-outline-primary{color:#00acc1;border-color:#00acc1}.btn-outline-primary:hover{color:#fff;background-color:#00acc1;border-color:#00acc1}.btn-outline-primary.focus,.btn-outline-primary:focus{box-shadow:0 0 0 .2rem rgba(0,172,193,.5)}.btn-outline-primary.disabled,.btn-outline-primary:disabled{color:#00acc1;background-color:transparent}.btn-outline-primary:not(:disabled):not(.disabled).active,.btn-outline-primary:not(:disabled):not(.disabled):active,.show>.btn-outline-primary.dropdown-toggle{color:#fff;background-color:#00acc1;border-color:#00acc1}.btn-outline-primary:not(:disabled):not(.disabled).active:focus,.btn-outline-primary:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-primary.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(0,172,193,.5)}.btn-outline-secondary{color:#212529;border-color:#212529}.btn-outline-secondary:hover{color:#fff;background-color:#212529;border-color:#212529}.btn-outline-secondary.focus,.btn-outline-secondary:focus{box-shadow:0 0 0 .2rem rgba(33,37,41,.5)}.btn-outline-secondary.disabled,.btn-outline-secondary:disabled{color:#212529;background-color:transparent}.btn-outline-secondary:not(:disabled):not(.disabled).active,.btn-outline-secondary:not(:disabled):not(.disabled):active,.show>.btn-outline-secondary.dropdown-toggle{color:#fff;background-color:#212529;border-color:#212529}.btn-outline-secondary:not(:disabled):not(.disabled).active:focus,.btn-outline-secondary:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-secondary.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(33,37,41,.5)}.btn-outline-success{color:#28a745;border-color:#28a745}.btn-outline-success:hover{color:#fff;background-color:#28a745;border-color:#28a745}.btn-outline-success.focus,.btn-outline-success:focus{box-shadow:0 0 0 .2rem rgba(40,167,69,.5)}.btn-outline-success.disabled,.btn-outline-success:disabled{color:#28a745;background-color:transparent}.btn-outline-success:not(:disabled):not(.disabled).active,.btn-outline-success:not(:disabled):not(.disabled):active,.show>.btn-outline-success.dropdown-toggle{color:#fff;background-color:#28a745;border-color:#28a745}.btn-outline-success:not(:disabled):not(.disabled).active:focus,.btn-outline-success:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-success.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(40,167,69,.5)}.btn-outline-info{color:#17a2b8;border-color:#17a2b8}.btn-outline-info:hover{color:#fff;background-color:#17a2b8;border-color:#17a2b8}.btn-outline-info.focus,.btn-outline-info:focus{box-shadow:0 0 0 .2rem rgba(23,162,184,.5)}.btn-outline-info.disabled,.btn-outline-info:disabled{color:#17a2b8;background-color:transparent}.btn-outline-info:not(:disabled):not(.disabled).active,.btn-outline-info:not(:disabled):not(.disabled):active,.show>.btn-outline-info.dropdown-toggle{color:#fff;background-color:#17a2b8;border-color:#17a2b8}.btn-outline-info:not(:disabled):not(.disabled).active:focus,.btn-outline-info:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-info.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(23,162,184,.5)}.btn-outline-warning{color:#ffc107;border-color:#ffc107}.btn-outline-warning:hover{color:#212529;background-color:#ffc107;border-color:#ffc107}.btn-outline-warning.focus,.btn-outline-warning:focus{box-shadow:0 0 0 .2rem rgba(255,193,7,.5)}.btn-outline-warning.disabled,.btn-outline-warning:disabled{color:#ffc107;background-color:transparent}.btn-outline-warning:not(:disabled):not(.disabled).active,.btn-outline-warning:not(:disabled):not(.disabled):active,.show>.btn-outline-warning.dropdown-toggle{color:#212529;background-color:#ffc107;border-color:#ffc107}.btn-outline-warning:not(:disabled):not(.disabled).active:focus,.btn-outline-warning:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-warning.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(255,193,7,.5)}.btn-outline-danger{color:#dc3545;border-color:#dc3545}.btn-outline-danger:hover{color:#fff;background-color:#dc3545;border-color:#dc3545}.btn-outline-danger.focus,.btn-outline-danger:focus{box-shadow:0 0 0 .2rem rgba(220,53,69,.5)}.btn-outline-danger.disabled,.btn-outline-danger:disabled{color:#dc3545;background-color:transparent}.btn-outline-danger:not(:disabled):not(.disabled).active,.btn-outline-danger:not(:disabled):not(.disabled):active,.show>.btn-outline-danger.dropdown-toggle{color:#fff;background-color:#dc3545;border-color:#dc3545}.btn-outline-danger:not(:disabled):not(.disabled).active:focus,.btn-outline-danger:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-danger.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(220,53,69,.5)}.btn-outline-light{color:#f8f9fa;border-color:#f8f9fa}.btn-outline-light:hover{color:#212529;background-color:#f8f9fa;border-color:#f8f9fa}.btn-outline-light.focus,.btn-outline-light:focus{box-shadow:0 0 0 .2rem rgba(248,249,250,.5)}.btn-outline-light.disabled,.btn-outline-light:disabled{color:#f8f9fa;background-color:transparent}.btn-outline-light:not(:disabled):not(.disabled).active,.btn-outline-light:not(:disabled):not(.disabled):active,.show>.btn-outline-light.dropdown-toggle{color:#212529;background-color:#f8f9fa;border-color:#f8f9fa}.btn-outline-light:not(:disabled):not(.disabled).active:focus,.btn-outline-light:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-light.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(248,249,250,.5)}.btn-outline-dark{color:#343a40;border-color:#343a40}.btn-outline-dark:hover{color:#fff;background-color:#343a40;border-color:#343a40}.btn-outline-dark.focus,.btn-outline-dark:focus{box-shadow:0 0 0 .2rem rgba(52,58,64,.5)}.btn-outline-dark.disabled,.btn-outline-dark:disabled{color:#343a40;background-color:transparent}.btn-outline-dark:not(:disabled):not(.disabled).active,.btn-outline-dark:not(:disabled):not(.disabled):active,.show>.btn-outline-dark.dropdown-toggle{color:#fff;background-color:#343a40;border-color:#343a40}.btn-outline-dark:not(:disabled):not(.disabled).active:focus,.btn-outline-dark:not(:disabled):not(.disabled):active:focus,.show>.btn-outline-dark.dropdown-toggle:focus{box-shadow:0 0 0 .2rem rgba(52,58,64,.5)}.btn-link{font-weight:400;color:#00acc1;text-decoration:none}.btn-link:hover{color:#006875;text-decoration:none}.btn-link.focus,.btn-link:focus{text-decoration:none}.btn-link.disabled,.btn-link:disabled{color:#6c757d;pointer-events:none}.btn-group-lg>.btn,.btn-lg{padding:.75rem 1.5rem;font-size:1.05rem;line-height:1.5;border-radius:.3rem}.btn-group-sm>.btn,.btn-sm{padding:.25rem .5rem;font-size:.875rem;line-height:1.5;border-radius:.2rem}.btn-block{display:block;width:100%}.btn-block+.btn-block{margin-top:.5rem}input[type=button].btn-block,input[type=reset].btn-block,input[type=submit].btn-block{width:100%}.fade{transition:opacity .15s linear}@media (prefers-reduced-motion:reduce){.fade{transition:none}}.fade:not(.show){opacity:0}.collapse:not(.show){display:none}.collapsing{position:relative;height:0;overflow:hidden;transition:height .35s ease}@media (prefers-reduced-motion:reduce){.collapsing{transition:none}}.dropdown,.dropleft,.dropright,.dropup{position:relative}.dropdown-toggle{white-space:nowrap}.dropdown-toggle::after{display:inline-block;margin-left:.255em;vertical-align:.255em;content:'';border-top:.3em solid;border-right:.3em solid transparent;border-bottom:0;border-left:.3em solid transparent}.dropdown-toggle:empty::after{margin-left:0}.dropdown-menu{position:absolute;top:100%;left:0;z-index:1000;display:none;float:left;min-width:10rem;padding:.5rem 0;margin:.125rem 0 0;font-size:1rem;color:#bbb;text-align:left;list-style:none;background-color:#fff;background-clip:padding-box;border:1px solid rgba(0,0,0,.15);border-radius:.25rem}.dropdown-menu-left{right:auto;left:0}.dropdown-menu-right{right:0;left:auto}@media (min-width:576px){.dropdown-menu-sm-left{right:auto;left:0}.dropdown-menu-sm-right{right:0;left:auto}}@media (min-width:768px){.dropdown-menu-md-left{right:auto;left:0}.dropdown-menu-md-right{right:0;left:auto}}@media (min-width:992px){.dropdown-menu-lg-left{right:auto;left:0}.dropdown-menu-lg-right{right:0;left:auto}}@media (min-width:1200px){.dropdown-menu-xl-left{right:auto;left:0}.dropdown-menu-xl-right{right:0;left:auto}}.dropup .dropdown-menu{top:auto;bottom:100%;margin-top:0;margin-bottom:.125rem}.dropup .dropdown-toggle::after{display:inline-block;margin-left:.255em;vertical-align:.255em;content:'';border-top:0;border-right:.3em solid transparent;border-bottom:.3em solid;border-left:.3em solid transparent}.dropup .dropdown-toggle:empty::after{margin-left:0}.dropright .dropdown-menu{top:0;right:auto;left:100%;margin-top:0;margin-left:.125rem}.dropright .dropdown-toggle::after{display:inline-block;margin-left:.255em;vertical-align:.255em;content:'';border-top:.3em solid transparent;border-right:0;border-bottom:.3em solid transparent;border-left:.3em solid}.dropright .dropdown-toggle:empty::after{margin-left:0}.dropright .dropdown-toggle::after{vertical-align:0}.dropleft .dropdown-menu{top:0;right:100%;left:auto;margin-top:0;margin-right:.125rem}.dropleft .dropdown-toggle::after{display:inline-block;margin-left:.255em;vertical-align:.255em;content:''}.dropleft .dropdown-toggle::after{display:none}.dropleft .dropdown-toggle::before{display:inline-block;margin-right:.255em;vertical-align:.255em;content:'';border-top:.3em solid transparent;border-right:.3em solid;border-bottom:.3em solid transparent}.dropleft .dropdown-toggle:empty::after{margin-left:0}.dropleft .dropdown-toggle::before{vertical-align:0}.dropdown-menu[x-placement^=bottom],.dropdown-menu[x-placement^=left],.dropdown-menu[x-placement^=right],.dropdown-menu[x-placement^=top]{right:auto;bottom:auto}.dropdown-divider{height:0;margin:.5rem 0;overflow:hidden;border-top:1px solid #e9ecef}.dropdown-item{display:block;width:100%;padding:.25rem 1.5rem;clear:both;font-weight:400;color:#212529;text-align:inherit;white-space:nowrap;background-color:transparent;border:0}.dropdown-item:focus,.dropdown-item:hover{color:#16181b;text-decoration:none;background-color:#f8f9fa}.dropdown-item.active,.dropdown-item:active{color:#fff;text-decoration:none;background-color:#343a40}.dropdown-item.disabled,.dropdown-item:disabled{color:#6c757d;pointer-events:none;background-color:transparent}.dropdown-menu.show{display:block}.dropdown-header{display:block;padding:.5rem 1.5rem;margin-bottom:0;font-size:.875rem;color:#6c757d;white-space:nowrap}.dropdown-item-text{display:block;padding:.25rem 1.5rem;color:#212529}.btn-group,.btn-group-vertical{position:relative;display:inline-flex;vertical-align:middle}.btn-group-vertical>.btn,.btn-group>.btn{position:relative;flex:1 1 auto}.btn-group-vertical>.btn:hover,.btn-group>.btn:hover{z-index:1}.btn-group-vertical>.btn.active,.btn-group-vertical>.btn:active,.btn-group-vertical>.btn:focus,.btn-group>.btn.active,.btn-group>.btn:active,.btn-group>.btn:focus{z-index:1}.btn-toolbar{display:flex;flex-wrap:wrap;justify-content:flex-start}.btn-toolbar .input-group{width:auto}.btn-group>.btn-group:not(:first-child),.btn-group>.btn:not(:first-child){margin-left:-1px}.btn-group>.btn-group:not(:last-child)>.btn,.btn-group>.btn:not(:last-child):not(.dropdown-toggle){border-top-right-radius:0;border-bottom-right-radius:0}.btn-group>.btn-group:not(:first-child)>.btn,.btn-group>.btn:not(:first-child){border-top-left-radius:0;border-bottom-left-radius:0}.dropdown-toggle-split{padding-right:.5625rem;padding-left:.5625rem}.dropdown-toggle-split::after,.dropright .dropdown-toggle-split::after,.dropup .dropdown-toggle-split::after{margin-left:0}.dropleft .dropdown-toggle-split::before{margin-right:0}.btn-group-sm>.btn+.dropdown-toggle-split,.btn-sm+.dropdown-toggle-split{padding-right:.375rem;padding-left:.375rem}.btn-group-lg>.btn+.dropdown-toggle-split,.btn-lg+.dropdown-toggle-split{padding-right:1.125rem;padding-left:1.125rem}.btn-group-vertical{flex-direction:column;align-items:flex-start;justify-content:center}.btn-group-vertical>.btn,.btn-group-vertical>.btn-group{width:100%}.btn-group-vertical>.btn-group:not(:first-child),.btn-group-vertical>.btn:not(:first-child){margin-top:-1px}.btn-group-vertical>.btn-group:not(:last-child)>.btn,.btn-group-vertical>.btn:not(:last-child):not(.dropdown-toggle){border-bottom-right-radius:0;border-bottom-left-radius:0}.btn-group-vertical>.btn-group:not(:first-child)>.btn,.btn-group-vertical>.btn:not(:first-child){border-top-left-radius:0;border-top-right-radius:0}.btn-group-toggle>.btn,.btn-group-toggle>.btn-group>.btn{margin-bottom:0}.btn-group-toggle>.btn input[type=checkbox],.btn-group-toggle>.btn input[type=radio],.btn-group-toggle>.btn-group>.btn input[type=checkbox],.btn-group-toggle>.btn-group>.btn input[type=radio]{position:absolute;clip:rect(0,0,0,0);pointer-events:none}.input-group{position:relative;display:flex;flex-wrap:wrap;align-items:stretch;width:100%}.input-group>.custom-file,.input-group>.custom-select,.input-group>.form-control,.input-group>.form-control-plaintext{position:relative;flex:1 1 auto;width:1%;min-width:0;margin-bottom:0}.input-group>.custom-file+.custom-file,.input-group>.custom-file+.custom-select,.input-group>.custom-file+.form-control,.input-group>.custom-select+.custom-file,.input-group>.custom-select+.custom-select,.input-group>.custom-select+.form-control,.input-group>.form-control+.custom-file,.input-group>.form-control+.custom-select,.input-group>.form-control+.form-control,.input-group>.form-control-plaintext+.custom-file,.input-group>.form-control-plaintext+.custom-select,.input-group>.form-control-plaintext+.form-control{margin-left:-1px}.input-group>.custom-file .custom-file-input:focus~.custom-file-label,.input-group>.custom-select:focus,.input-group>.form-control:focus{z-index:3}.input-group>.custom-file .custom-file-input:focus{z-index:4}.input-group>.custom-select:not(:last-child),.input-group>.form-control:not(:last-child){border-top-right-radius:0;border-bottom-right-radius:0}.input-group>.custom-select:not(:first-child),.input-group>.form-control:not(:first-child){border-top-left-radius:0;border-bottom-left-radius:0}.input-group>.custom-file{display:flex;align-items:center}.input-group>.custom-file:not(:last-child) .custom-file-label,.input-group>.custom-file:not(:last-child) .custom-file-label::after{border-top-right-radius:0;border-bottom-right-radius:0}.input-group>.custom-file:not(:first-child) .custom-file-label{border-top-left-radius:0;border-bottom-left-radius:0}.input-group-append,.input-group-prepend{display:flex}.input-group-append .btn,.input-group-prepend .btn{position:relative;z-index:2}.input-group-append .btn:focus,.input-group-prepend .btn:focus{z-index:3}.input-group-append .btn+.btn,.input-group-append .btn+.input-group-text,.input-group-append .input-group-text+.btn,.input-group-append .input-group-text+.input-group-text,.input-group-prepend .btn+.btn,.input-group-prepend .btn+.input-group-text,.input-group-prepend .input-group-text+.btn,.input-group-prepend .input-group-text+.input-group-text{margin-left:-1px}.input-group-prepend{margin-right:-1px}.input-group-append{margin-left:-1px}.input-group-text{display:flex;align-items:center;padding:.375rem .75rem;margin-bottom:0;font-size:1rem;font-weight:400;line-height:1.5;color:#adb5bd;text-align:center;white-space:nowrap;background-color:#e9ecef;border:1px solid #282d31;border-radius:.25rem}.input-group-text input[type=checkbox],.input-group-text input[type=radio]{margin-top:0}.input-group-lg>.custom-select,.input-group-lg>.form-control:not(textarea){height:calc(1.5em + 1.5rem + 2px)}.input-group-lg>.custom-select,.input-group-lg>.form-control,.input-group-lg>.input-group-append>.btn,.input-group-lg>.input-group-append>.input-group-text,.input-group-lg>.input-group-prepend>.btn,.input-group-lg>.input-group-prepend>.input-group-text{padding:.75rem 1.5rem;font-size:1rem;line-height:1.5;border-radius:.3rem}.input-group-sm>.custom-select,.input-group-sm>.form-control:not(textarea){height:calc(1.5em + .5rem + 2px)}.input-group-sm>.custom-select,.input-group-sm>.form-control,.input-group-sm>.input-group-append>.btn,.input-group-sm>.input-group-append>.input-group-text,.input-group-sm>.input-group-prepend>.btn,.input-group-sm>.input-group-prepend>.input-group-text{padding:.25rem .5rem;font-size:.875rem;line-height:1.5;border-radius:.2rem}.input-group-lg>.custom-select,.input-group-sm>.custom-select{padding-right:1.75rem}.input-group>.input-group-append:last-child>.btn:not(:last-child):not(.dropdown-toggle),.input-group>.input-group-append:last-child>.input-group-text:not(:last-child),.input-group>.input-group-append:not(:last-child)>.btn,.input-group>.input-group-append:not(:last-child)>.input-group-text,.input-group>.input-group-prepend>.btn,.input-group>.input-group-prepend>.input-group-text{border-top-right-radius:0;border-bottom-right-radius:0}.input-group>.input-group-append>.btn,.input-group>.input-group-append>.input-group-text,.input-group>.input-group-prepend:first-child>.btn:not(:first-child),.input-group>.input-group-prepend:first-child>.input-group-text:not(:first-child),.input-group>.input-group-prepend:not(:first-child)>.btn,.input-group>.input-group-prepend:not(:first-child)>.input-group-text{border-top-left-radius:0;border-bottom-left-radius:0}.custom-control{position:relative;display:block;min-height:1.5rem;padding-left:1.5rem}.custom-control-inline{display:inline-flex;margin-right:1rem}.custom-control-input{position:absolute;left:0;z-index:-1;width:1rem;height:1.25rem;opacity:0}.custom-control-input:checked~.custom-control-label::before{color:#fff;border-color:#343a40;background-color:#343a40}.custom-control-input:focus~.custom-control-label::before{box-shadow:0 0 0 .2rem rgba(52,58,64,.25)}.custom-control-input:focus:not(:checked)~.custom-control-label::before{border-color:#434b53}.custom-control-input:not(:disabled):active~.custom-control-label::before{color:#fff;background-color:#88939e;border-color:#88939e}.custom-control-input:disabled~.custom-control-label,.custom-control-input[disabled]~.custom-control-label{color:#6c757d}.custom-control-input:disabled~.custom-control-label::before,.custom-control-input[disabled]~.custom-control-label::before{background-color:#16181b}.custom-control-label{position:relative;margin-bottom:0;vertical-align:top}.custom-control-label::before{position:absolute;top:.25rem;left:-1.5rem;display:block;width:1rem;height:1rem;pointer-events:none;content:'';background-color:#212529;border:#adb5bd solid 1px}.custom-control-label::after{position:absolute;top:.25rem;left:-1.5rem;display:block;width:1rem;height:1rem;content:'';background:no-repeat 50%/50% 50%}.custom-checkbox .custom-control-label::before{border-radius:.25rem}.custom-checkbox .custom-control-input:checked~.custom-control-label::after{background-image:url('data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e')}.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::before{border-color:#343a40;background-color:#343a40}.custom-checkbox .custom-control-input:indeterminate~.custom-control-label::after{background-image:url('data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='4' viewBox='0 0 4 4'%3e%3cpath stroke='%23fff' d='M0 2h4'/%3e%3c/svg%3e')}.custom-checkbox .custom-control-input:disabled:checked~.custom-control-label::before{background-color:rgba(0,172,193,.5)}.custom-checkbox .custom-control-input:disabled:indeterminate~.custom-control-label::before{background-color:rgba(0,172,193,.5)}.custom-radio .custom-control-label::before{border-radius:50%}.custom-radio .custom-control-input:checked~.custom-control-label::after{background-image:url('data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e')}.custom-radio .custom-control-input:disabled:checked~.custom-control-label::before{background-color:rgba(0,172,193,.5)}.custom-switch{padding-left:2.25rem}.custom-switch .custom-control-label::before{left:-2.25rem;width:1.75rem;pointer-events:all;border-radius:.5rem}.custom-switch .custom-control-label::after{top:calc(.25rem + 2px);left:calc(-2.25rem + 2px);width:calc(1rem - 4px);height:calc(1rem - 4px);background-color:#adb5bd;border-radius:.5rem;transition:transform .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out}@media (prefers-reduced-motion:reduce){.custom-switch .custom-control-label::after{transition:none}}.custom-switch .custom-control-input:checked~.custom-control-label::after{background-color:#212529;transform:translateX(.75rem)}.custom-switch .custom-control-input:disabled:checked~.custom-control-label::before{background-color:rgba(0,172,193,.5)}.custom-select{display:inline-block;width:100%;height:calc(1.5em + .75rem + 2px);padding:.375rem 1.75rem .375rem .75rem;font-size:1rem;font-weight:400;line-height:1.5;color:#adb5bd;vertical-align:middle;background:#212529 url('data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e') no-repeat right .75rem center/8px 10px;border:1px solid #282d31;border-radius:.25rem;appearance:none}.custom-select:focus{border-color:#434b53;outline:0;box-shadow:0 0 0 .2rem rgba(52,58,64,.25)}.custom-select:focus::-ms-value{color:#adb5bd;background-color:#212529}.custom-select[multiple],.custom-select[size]:not([size='1']){height:auto;padding-right:.75rem;background-image:none}.custom-select:disabled{color:#6c757d;background-color:#e9ecef}.custom-select::-ms-expand{display:none}.custom-select:-moz-focusring{color:transparent;text-shadow:0 0 0 #adb5bd}.custom-select-sm{height:calc(1.5em + .5rem + 2px);padding-top:.25rem;padding-bottom:.25rem;padding-left:.5rem;font-size:.875rem}.custom-select-lg{height:calc(1.5em + 1.5rem + 2px);padding-top:.75rem;padding-bottom:.75rem;padding-left:1.5rem;font-size:1rem}.custom-file{position:relative;display:inline-block;width:100%;height:calc(1.5em + .75rem + 2px);margin-bottom:0}.custom-file-input{position:relative;z-index:2;width:100%;height:calc(1.5em + .75rem + 2px);margin:0;opacity:0}.custom-file-input:focus~.custom-file-label{border-color:#434b53;box-shadow:0 0 0 .2rem rgba(52,58,64,.25)}.custom-file-input:disabled~.custom-file-label,.custom-file-input[disabled]~.custom-file-label{background-color:#16181b}.custom-file-input:lang(fr)~.custom-file-label::after{content:'Browse'}.custom-file-input~.custom-file-label[data-browse]::after{content:attr(data-browse)}.custom-file-label{position:absolute;top:0;right:0;left:0;z-index:1;height:calc(1.5em + .75rem + 2px);padding:.375rem .75rem;font-weight:400;line-height:1.5;color:#adb5bd;background-color:#212529;border:1px solid #282d31;border-radius:.25rem}.custom-file-label::after{position:absolute;top:0;right:0;bottom:0;z-index:3;display:block;height:calc(1.5em + .75rem);padding:.375rem .75rem;line-height:1.5;color:#adb5bd;content:'Browse';background-color:#e9ecef;border-left:inherit;border-radius:0 .25rem .25rem 0}.custom-range{width:100%;height:1.4rem;padding:0;background-color:transparent;appearance:none}.custom-range:focus{outline:0}.custom-range:focus::-webkit-slider-thumb{box-shadow:0 0 0 1px #111,0 0 0 .2rem rgba(52,58,64,.25)}.custom-range:focus::-moz-range-thumb{box-shadow:0 0 0 1px #111,0 0 0 .2rem rgba(52,58,64,.25)}.custom-range:focus::-ms-thumb{box-shadow:0 0 0 1px #111,0 0 0 .2rem rgba(52,58,64,.25)}.custom-range::-moz-focus-outer{border:0}.custom-range::-webkit-slider-thumb{width:1rem;height:1rem;margin-top:-.25rem;background-color:#343a40;border:0;border-radius:1rem;transition:background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;appearance:none}@media (prefers-reduced-motion:reduce){.custom-range::-webkit-slider-thumb{transition:none}}.custom-range::-webkit-slider-thumb:active{background-color:#88939e}.custom-range::-webkit-slider-runnable-track{width:100%;height:.5rem;color:transparent;cursor:pointer;background-color:#dee2e6;border-color:transparent;border-radius:1rem}.custom-range::-moz-range-thumb{width:1rem;height:1rem;background-color:#343a40;border:0;border-radius:1rem;transition:background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;appearance:none}@media (prefers-reduced-motion:reduce){.custom-range::-moz-range-thumb{transition:none}}.custom-range::-moz-range-thumb:active{background-color:#88939e}.custom-range::-moz-range-track{width:100%;height:.5rem;color:transparent;cursor:pointer;background-color:#dee2e6;border-color:transparent;border-radius:1rem}.custom-range::-ms-thumb{width:1rem;height:1rem;margin-top:0;margin-right:.2rem;margin-left:.2rem;background-color:#343a40;border:0;border-radius:1rem;transition:background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;appearance:none}@media (prefers-reduced-motion:reduce){.custom-range::-ms-thumb{transition:none}}.custom-range::-ms-thumb:active{background-color:#88939e}.custom-range::-ms-track{width:100%;height:.5rem;color:transparent;cursor:pointer;background-color:transparent;border-color:transparent;border-width:.5rem}.custom-range::-ms-fill-lower{background-color:#dee2e6;border-radius:1rem}.custom-range::-ms-fill-upper{margin-right:15px;background-color:#dee2e6;border-radius:1rem}.custom-range:disabled::-webkit-slider-thumb{background-color:#adb5bd}.custom-range:disabled::-webkit-slider-runnable-track{cursor:default}.custom-range:disabled::-moz-range-thumb{background-color:#adb5bd}.custom-range:disabled::-moz-range-track{cursor:default}.custom-range:disabled::-ms-thumb{background-color:#adb5bd}.custom-control-label::before,.custom-file-label,.custom-select{transition:background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out}@media (prefers-reduced-motion:reduce){.custom-control-label::before,.custom-file-label,.custom-select{transition:none}}.nav{display:flex;flex-wrap:wrap;padding-left:0;margin-bottom:0;list-style:none}.nav-link{display:block;padding:.5rem 1rem}.nav-link:focus,.nav-link:hover{text-decoration:none}.nav-link.disabled{color:#6c757d;pointer-events:none;cursor:default}.nav-tabs{border-bottom:1px solid #dee2e6}.nav-tabs .nav-item{margin-bottom:-1px}.nav-tabs .nav-link{border:1px solid transparent;border-top-left-radius:.25rem;border-top-right-radius:.25rem}.nav-tabs .nav-link:focus,.nav-tabs .nav-link:hover{border-color:#e9ecef #e9ecef #dee2e6}.nav-tabs .nav-link.disabled{color:#6c757d;background-color:transparent;border-color:transparent}.nav-tabs .nav-item.show .nav-link,.nav-tabs .nav-link.active{color:#495057;background-color:#111;border-color:#dee2e6 #dee2e6 #111}.nav-tabs .dropdown-menu{margin-top:-1px;border-top-left-radius:0;border-top-right-radius:0}.nav-pills .nav-link{border-radius:.25rem}.nav-pills .nav-link.active,.nav-pills .show>.nav-link{color:#fff;background-color:#343a40}.nav-fill .nav-item{flex:1 1 auto;text-align:center}.nav-justified .nav-item{flex-basis:0;flex-grow:1;text-align:center}.tab-content>.tab-pane{display:none}.tab-content>.active{display:block}.navbar{position:relative;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;padding:.5rem 1rem}.navbar .container,.navbar .container-fluid,.navbar .container-lg,.navbar .container-md,.navbar .container-sm,.navbar .container-xl{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between}.navbar-brand{display:inline-block;padding-top:.3125rem;padding-bottom:.3125rem;margin-right:1rem;font-size:1.25rem;line-height:inherit;white-space:nowrap}.navbar-brand:focus,.navbar-brand:hover{text-decoration:none}.navbar-nav{display:flex;flex-direction:column;padding-left:0;margin-bottom:0;list-style:none}.navbar-nav .nav-link{padding-right:0;padding-left:0}.navbar-nav .dropdown-menu{position:static;float:none}.navbar-text{display:inline-block;padding-top:.5rem;padding-bottom:.5rem}.navbar-collapse{flex-basis:100%;flex-grow:1;align-items:center}.navbar-toggler{padding:.25rem .75rem;font-size:1.25rem;line-height:1;background-color:transparent;border:1px solid transparent;border-radius:.25rem}.navbar-toggler:focus,.navbar-toggler:hover{text-decoration:none}.navbar-toggler-icon{display:inline-block;width:1.5em;height:1.5em;vertical-align:middle;content:'';background:no-repeat center center;background-size:100% 100%}@media (max-width:575.98px){.navbar-expand-sm>.container,.navbar-expand-sm>.container-fluid,.navbar-expand-sm>.container-lg,.navbar-expand-sm>.container-md,.navbar-expand-sm>.container-sm,.navbar-expand-sm>.container-xl{padding-right:0;padding-left:0}}@media (min-width:576px){.navbar-expand-sm{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-sm .navbar-nav{flex-direction:row}.navbar-expand-sm .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-sm .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-sm>.container,.navbar-expand-sm>.container-fluid,.navbar-expand-sm>.container-lg,.navbar-expand-sm>.container-md,.navbar-expand-sm>.container-sm,.navbar-expand-sm>.container-xl{flex-wrap:nowrap}.navbar-expand-sm .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-sm .navbar-toggler{display:none}}@media (max-width:767.98px){.navbar-expand-md>.container,.navbar-expand-md>.container-fluid,.navbar-expand-md>.container-lg,.navbar-expand-md>.container-md,.navbar-expand-md>.container-sm,.navbar-expand-md>.container-xl{padding-right:0;padding-left:0}}@media (min-width:768px){.navbar-expand-md{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-md .navbar-nav{flex-direction:row}.navbar-expand-md .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-md .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-md>.container,.navbar-expand-md>.container-fluid,.navbar-expand-md>.container-lg,.navbar-expand-md>.container-md,.navbar-expand-md>.container-sm,.navbar-expand-md>.container-xl{flex-wrap:nowrap}.navbar-expand-md .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-md .navbar-toggler{display:none}}@media (max-width:991.98px){.navbar-expand-lg>.container,.navbar-expand-lg>.container-fluid,.navbar-expand-lg>.container-lg,.navbar-expand-lg>.container-md,.navbar-expand-lg>.container-sm,.navbar-expand-lg>.container-xl{padding-right:0;padding-left:0}}@media (min-width:992px){.navbar-expand-lg{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-lg .navbar-nav{flex-direction:row}.navbar-expand-lg .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-lg .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-lg>.container,.navbar-expand-lg>.container-fluid,.navbar-expand-lg>.container-lg,.navbar-expand-lg>.container-md,.navbar-expand-lg>.container-sm,.navbar-expand-lg>.container-xl{flex-wrap:nowrap}.navbar-expand-lg .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-lg .navbar-toggler{display:none}}@media (max-width:1199.98px){.navbar-expand-xl>.container,.navbar-expand-xl>.container-fluid,.navbar-expand-xl>.container-lg,.navbar-expand-xl>.container-md,.navbar-expand-xl>.container-sm,.navbar-expand-xl>.container-xl{padding-right:0;padding-left:0}}@media (min-width:1200px){.navbar-expand-xl{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand-xl .navbar-nav{flex-direction:row}.navbar-expand-xl .navbar-nav .dropdown-menu{position:absolute}.navbar-expand-xl .navbar-nav .nav-link{padding-right:.5rem;padding-left:.5rem}.navbar-expand-xl>.container,.navbar-expand-xl>.container-fluid,.navbar-expand-xl>.container-lg,.navbar-expand-xl>.container-md,.navbar-expand-xl>.container-sm,.navbar-expand-xl>.container-xl{flex-wrap:nowrap}.navbar-expand-xl .navbar-collapse{display:flex!important;flex-basis:auto}.navbar-expand-xl .navbar-toggler{display:none}}.navbar-expand{flex-flow:row nowrap;justify-content:flex-start}.navbar-expand>.container,.navbar-expand>.container-fluid,.navbar-expand>.container-lg,.navbar-expand>.container-md,.navbar-expand>.container-sm,.navbar-expand>.container-xl{padding-right:0;padding-left:0}.navbar-expand .navbar-nav{flex-direction:row}.navbar-expand .navbar-nav .dropdown-menu{position:absolute}
  </style>
  <link
    rel='stylesheet'
    id='font-awesome-public_css-css'
    href='http://haytex.epizy.com/css/font-awesome.css'
    type='text/css'
    media='all'
  />
  <link
    rel='stylesheet'
    id='material-public-css-css'
    href='https://allmoviesforyou.net/wp-content/themes/toroflix/public/css/material.css?ver=1.2.0'
    type='text/css'
    media='all'
  />
  <script type='text/javascript'>
    /* <![CDATA[ */
    var toroflixPublic = {
      noItemsAvailable: 'Aucune entrée trouvée',
      selectAll: 'Tout sélectionner',
      selectNone: 'Désélectionner',
      searchplaceholder: 'Cliquer pour rechercher',
      loadingData: 'Still loading data...',
      viewmore: 'Voir plus',
      id: '',
      type: '',
    };
    /* ]]> */
  </script>
  <script>
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://haytex.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
</script>
<script
    type='text/javascript'
    src='http://haytex.epizy.com/js/jquery.js'
    id='funciones_public_jquery-js'
  ></script>
  <script
    type='text/javascript'
    src='http://haytex.epizy.com/js/owl.carousel.min.js'
    id='funciones_public_carousel-js'
  ></script>
  <script
    type='text/javascript'
    src='http://haytex.epizy.com/js/sol.js'
  ></script>
  <script
    type='text/javascript'
    src='http://haytex.epizy.com/js/fonction.js'
  ></script>
  <script src='https://s1.bunnycdn.ru/assets/template_1/min/all.js?6379b4a8' async=''></script>
</body>

";



    // fermer le fichier
    fclose($handle); }
// créer une nouvelle page pour chaque épisode
$filename = 'page_princ.php';
$handle = fopen($filename, 'w');
fwrite($handle, $page_princ);

// fermer le fichier
fclose($handle);
              
                echo '<input type="submit" name="submit_links" value="Confirmer les liens" />';
                echo '</form>';






//-----------------------------------------------------------PAGE PRINCIPALE----------------------------------------------------------------------//

$episode_numbers = array();
$image_urls = array();
foreach ($data['episodes'] as $episode) {
    $episode_number = $episode['episode_number'];
    $episode_numbers[] = $episode_number;
    $episode_name = $episode['name']; 
    $image_url = "https://image.tmdb.org/t/p/w500" . $episode["still_path"];
    $image_urls[] = $image_url;
    $total_seasons = $data['number_of_seasons'];
    $total_episodes = $data['number_of_episodes'];
    $note = $data['vote_average'];

  };


//-----------------------------------------------------------PAGE PRINCIPALE----------------------------------------------------------------------//









            } else {
                echo "Aucun épisode trouvé pour cette série TV.";
            }
        } else {
            echo '<form method="post">';
            echo '<label for="series_id">ID de la série TV :</label>';
            echo '<input type="number" name="series_id" id="series_id" placeholder="123456" required />';
            echo '<label for="season_number">Numéro de la saison :</label>';
            echo '<input type="number" name="season_number" id="season_number" placeholder="Numéro de la saison" required />';
            echo '<input type="submit" value="Rechercher la saison" />';
            echo '</form>';
        }
    ?>


<style>
form {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 50px;
}

label {
  margin: 10px 0;
  font-size: 18px;
}

input[type="number"], input[type="text"] {
  padding: 10px;
  border-radius: 5px;
  border: none;
  box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
  font-size: 16px;
}

input[type="submit"] {
  padding: 10px;
  border-radius: 5px;
  border: none;
  box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
  font-size: 16px;
  background-color: #007aff;
  color: #fff;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

input[type="submit"]:hover {
  background-color: #0055ff;
}

/* Style pour les titres */
h1, h2 {
    font-family: Arial, sans-serif;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-top: 40px;
    margin-bottom: 20px;
}

/* Style pour le formulaire d'entrée de l'ID et du numéro de saison */
form:first-of-type {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 40px;
}

form:first-of-type input[type="number"] {
    width: 300px;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: none;
    box-shadow: 0px 0px 5px 1px rgba(0,0,0,0.2);
}

/* Style pour la barre entre les deux formulaires */
hr {
    border: none;
    border-top: 1px solid #ccc;
    margin: 40px auto;
    width: 80%;
}

/* Style pour le formulaire d'entrée des liens uqload */
form:last-of-type {
    display: flex;
    flex-direction: column;
    align-items: center;
}

form:last-of-type label {
    font-family: Arial, sans-serif;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px;
}

form:last-of-type input[type="text"] {
    width: 500px;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: none;
    box-shadow: 0px 0px 5px 1px rgba(0,0,0,0.2);
}

form:last-of-type input[type="submit"] {
    background-color: #008CBA;
    color: white;
    font-size: 16px;
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}

form:last-of-type input[type="submit"]:hover {
    background-color: #006B8F;
}
</style>
