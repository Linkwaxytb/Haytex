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
    require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
    if (isset($_POST['series_id']) && isset($_POST['season_number'])) {
        $series_id = $_POST['series_id'];
        $season_number = $_POST['season_number'];
        $api_key     = '632577cc36b03c82c4167164f4edd49f';
        $url_princ   = "https://api.themoviedb.org/3/tv/$series_id?api_key=$api_key&append_to_response=credits&language=fr";
        $url         = "https://api.themoviedb.org/3/tv/$series_id/season/$season_number?api_key=$api_key&language=fr";
        $note = $serie_data['vote_average'];
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
        $video_url = "https://api.themoviedb.org/3/tv/$series_id/videos?api_key=$api_key&language=fr";
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
		$trailer_url   = 'https://www.youtube.com/watch?v=' . $trailer_key;
                 // transformer $lien_page en minuscules
    $lien_page = strtolower($serie_name);
    // supprimer les accents et les ponctuations
    $lien_page = preg_replace('/[\p{P}\p{S}]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', $lien_page));
    // remplacer les espaces par des underscores
    $lien_page = str_replace(' ', '_', $lien_page);
        $release_date  = $data['release_date'];
        $date          = date('Y', strtotime($release_date));
        $url_serie = 'http://haytex.epizy.com/series/' . $lien_page;
        // construction de l'URL de l'image de présentation et de l'image de fond
        $serie_poster_url = 'https://image.tmdb.org/t/p/w500' . $serie_poster_path;
        $serie_backdrop_url = 'https://image.tmdb.org/t/p/w1280' . $serie_backdrop_path;
        $season_number = $_POST['season_number'];


    $page_princ = "
<?php require '../../usersc/instructions1.php'; ?>   

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

$page_princ .="<a
                        href='". $trailer_url ."'
                        target='_blank'
                        class='Button TPlay AAIco-play_circle_outline'
                      ><strong>Bande-Annonce</strong></a
                  >
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
<?php include('../../php/series/episodes/$lien_page-$season_number.php')?>
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
          
        $url_prop = "https://api.themoviedb.org/3/tv/$series_id/recommendations?api_key=$api_key&language=fr";
        // Effectuer la requête à l'API
        $response = file_get_contents($url_prop);

// Vérifier si la requête a réussi
if ($response !== false) {
    // Convertir la réponse JSON en tableau associatif
    $recommendations = json_decode($response, true);

    // Parcourir les recommandations
    foreach ($recommendations['results'] as $recommendation) {
        $title_prop = $recommendation['name'];
        $poster_prop = $recommendation['poster_path'];
        $imageUrl_prop = "https://image.tmdb.org/t/p/w200$poster_prop";
        $isSeries = $recommendation['media_type'] === 'tv';
        // transformer $lien_page en minuscules
    $serie_url = strtolower($title_prop);

    // supprimer les accents et les ponctuations
    $serie_url = preg_replace('/[\p{P}\p{S}]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', $serie_url));

    // remplacer les espaces par des underscores
    $serie_url = str_replace(' ', '_', $serie_url);

            $page_princ .= "
        <div class='TPostMv'>
            <div class='TPost B'>
                <a href='http://haytex.epizy.com/series/" . $serie_url . "'>
                    <div class='Image'>
                        <figure class='Objf TpMvPlay AAIco-play_arrow'>
                            <img loading='lazy' class='owl-lazy' data-src='" . $imageUrl_prop . "' alt='" . $title_prop . "' />
                        </figure>
                        <span class='Qlty'>SERIE</span>
                    </div>
                    <h2 class='Title'>" . $title_prop . "</h2>
                </a>
            </div>
        </div>";
        };
}
    


        $page_princ .="
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
  

<div id='disqus_recommendations'><iframe id='dsq-app8627' name='dsq-app8627' allowtransparency='true' frameborder='0' scrolling='no' tabindex='0' title='Disqus' width='100%' src='https://disqus.com/recommendations/?base=default&amp;f=haytex&amp;t_u=http%3A%2F%2Fhaytex.epizy.com%2Findex%2F&amp;t_d=Haytex%20%7C%20Acceuil&amp;t_t=Haytex%20%7C%20Acceuil#version=3b8336c2a620b47aa2fac91f7787d2b1' style='width: 100% !important; border: none !important; overflow: hidden !important; height: 0px !important; display: inline !important; box-sizing: border-box !important;' horizontalscrolling='no' verticalscrolling='no'></iframe></div>
                    <script> 
(function() { // REQUIRED CONFIGURATION VARIABLE: EDIT THE SHORTNAME BELOW
var d = document, s = d.createElement('script'); // IMPORTANT: Replace EXAMPLE with your forum shortname!
s.src ='https://haytex.disqus.com/recommendations.js'; s.setAttribute('data-timestamp', +new Date());
(d.head || d.body).appendChild(s);
})();
</script>




<!--NE PAS TOUCHER-->
  <style type='text/css'> :root{ --body: #0b0c0c; --text: #bfc1c3; --link: #ffffff; --primary: 690DAB; } </style>
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
  <link rel='icon' href='http://haytex.epizy.com/Haytex_logo.png' sizes='192x192'>

  <style id='tp_style_css' type='text/css'>
[class*=fa-]:before{font-family:'Font Awesome 5 Pro';font-weight:900;-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:inline-block;font-style:normal;font-variant:normal;text-rendering:auto}.far:before,.fab:before{font-weight:400}i[class*=fa-]{display:inline-block}.fab:before{font-family:'Font Awesome 5 Brands'}
  </style>
  <link rel='stylesheet' id='font-awesome-public_css-css' href='http://haytex.epizy.com/css/font-awesome.css' type='text/css' media='all'>
  <link rel='stylesheet' id='material-public-css-css' href='https://allmoviesforyou.net/wp-content/themes/toroflix/public/css/material.css?ver=1.2.0' type='text/css' media='all'>
  <script>
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://haytex.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
</script>
<script src='https://s1.bunnycdn.ru/assets/template_1/min/all.js?6379b4a8' async=''></script>
</body></html>
"; 

// Créer le dossier
$dossierSerie = '../../series/'. $lien_page .'/' ;
if (!is_dir($dossierSerie)) {
    mkdir($dossierSerie, 0777, true);
}

// Créer le fichier index.php
$filename = $dossierSerie . 'index.php';
$handle   = fopen($filename, 'w');
                fwrite($handle, $page_princ);

                // fermer le fichier
                fclose($handle);
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
  $liste .= "
<section>
<div class='Top AAIco-list'>
<div class='Title'>Sélection épisode</div></br></br>

<div class='seasons aa-crd' x-data='{ tab: 0 }'><div class='seasons-bx' @click='tab = 0'><div :class='{ \"seasons-tt aa-crd-lnk\": true, \"on\": tab == 0 }' x-clock='' class='seasons-tt aa-crd-lnk on'><figure><img src='$serie_poster_url' loading='lazy' alt='$serie_name Saison $season_number'></figure><div><p>Saison <span>$season_number</span> <i class='fa-chevron-down'></i></p><span class='date'>$total_episodes Episodes </span></div></div>

<ul class='seasons-lst anm-a'>
"; 

 if (isset($data['episodes'])) {
   


    // afficher un formulaire pour chaque épisode
    echo '<form method="post">';
    foreach ($data['episodes'] as $index => $episode) {
        $episode_number = $episode['episode_number'];
        $episode_number1 = $episode['episode_number'] - 1;
        $episode_number2 = $episode['episode_number'] + 1;
        $episode_name   = $episode['name']; 
        $image_url      = "https://image.tmdb.org/t/p/w500" . $episode["still_path"];
        $air_date = $episode['air_date'];
        $formatted_date = date('j M. Y', strtotime($air_date));

        $liste .="<li><div><div><figure class='fa-play-circle'><img class='brd1 poa' src='$image_url' loading='lazy' alt='Episode $episode_number'></figure><h3 class='title'><span>S$season_number-E$episode_number</span> $episode_name - VF</h3></div><div><span class='date'>$formatted_date</span><a href='http://haytex.epizy.com/series/$lien_page/". $season_number ."x". $episode_number .".php' class='btn sm rnd'>Regarder l'épisode</a></div></div></li>";



 $links = $_POST['links'];
        
        $episode_page = "
        <?php require '../../usersc/instructions1.php'; ?>  
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
<div class='Container'>
<div class='optns-bx'>
            <div class='drpdn'>
                <button class='bstd Button'>
                    <span>VOSTFR <span>Language</span></span>
                    <i class='fa-chevron-down'></i>
                </button>
                <ul class='optnslst trsrcbx'>
                    <li>
                        <button data-embed='LIENNNNN' class='Button sgty'>
                            <span class='nmopt'>01</span>
                            <span>VOSTFR <span>HD • LECTEUR 1</span></span>
                        </button>
                    </li>
                    <li>
                        <button data-embed='LIENNNNN' class='Button sgty'>
                            <span class='nmopt'>02</span>
                            <span>VOSTFR <span>HD • LECTEUR 2</span></span>
                        </button>
                    </li>
                </ul>
            </div>
            <div class='drpdn'>
                <button class='bstd Button'>
                    <span>VF <span>Language</span></span>
                    <i class='fa-chevron-down'></i>
                </button>
                <ul class='optnslst trsrcbx'>
                    <li>
                        <button data-embed='LIENNNNN' class='Button sgty on'>
                            <span class='nmopt'>01</span>
                            <span>VF <span>HD • LECTEUR 1</span></span>
                        </button>
                    </li>
                    <li>
                        <button data-embed='LIENNNN' class='Button sgty'>
                            <span class='nmopt'>02</span>
                            <span>VF <span>HD • LECTEUR 2</span></span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class='VideoPlayer'>
            <div id='VideoOption01' class='Video on' style=''>
                <iframe id='myIframe' src='LIENNNN' allowfullscreen='' frameborder='0'></iframe>
            </div>
            <span class='BtnLight AAIco-lightbulb_outline lgtbx-lnk'></span>
            <span class='lgtbx'></span>
        </div>
    <div class='navepi tagcloud'>
";
// Vérifier si l'épisode est le premier de toute la série
if ($season_number == 1 && $episode_number == 1) {
    // Afficher un bouton spécial pour le premier épisode
    $episode_page .= '<a href="#r" class="prev off"><span>Episode précédent </span></a><br>';
} else {
    // Afficher un bouton pour revenir à l'épisode précédent
    $episode_page .= '<a href="http://haytex.epizy.com/series/'. $lien_page .'/'. $season_number .'x'. $episode_number1 .'" class=\'prev\'><span>Episode précédent </span></a><br>';
};

$episode_page .="<a href=\"http://haytex.epizy.com/series/". $lien_page ."/\" class=\"list prev\"><span>Episodes</span></a>
<a href=\"http://haytex.epizy.com/series/". $lien_page ."/". $season_number ."x". $episode_number2 ."\" class=\"next\"> <span>Episode suivant</span> </a>
</div> </div>
<div class=\"Image\">
<figure class=\"Objf\"><img src= '$serie_backdrop_url' alt=\"Background\"></figure>
</div>
</div>
<script >var buttons = document.querySelectorAll('.Button.sgty');
        var myIframe = document.getElementById('myIframe');

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Réinitialiser la classe pour tous les boutons
                buttons.forEach(function(btn) {
                    btn.classList.remove('on');
                    btn.classList.add('sgty');
                });

                // Mettre à jour la classe pour le bouton sélectionné
                this.classList.remove('sgty');
                this.classList.add('on');

                var embedURL = this.getAttribute('data-embed');
                myIframe.src = embedURL;
            });
        });</script>

</div> <div class=\"Body\"><div class=\"Main Container\"> <div class=\"TpRwCont \"> <main> <article class=\"TPost A\">
<header class=\"Container\">
<div class=\"TPMvCn\">
<h1 class=\"Title\" style=\"color:white\">". $serie_name ." EP". $episode_number ." S". $season_number ." VF</h1>
<div class=\"Info\">
<span class=\"Date\">$formatted_date</span><a href=\"http://haytex.epizy.com/series/$lien_page/\">Tous les épisodes</a>
</div>
<div class=\"Description\">";
//Réalisateurs
        foreach ($crew as $member) {
            if ($member['job'] == 'Director') {
                $directors[] = $member['name'];
            }
        }
        if (!empty($directors)) {
            $episode_page .= "<p class='Director'><span>Réalisation:</span> ";
            foreach ($directors as $director) {
                $episode_page .= "<a href='http://haytex.epizy.com/director/" . urlencode($director) . "' target='_blank'>" . $director . "</a>, ";
            }
            $episode_page = rtrim($episode_page, ', ');
            $episode_page .= "</p>";
        }
        //Acteurs
        $episode_page .= "<p class='Cast Cast-sh oh'>
        <span>Casting principal:</span> ";
        foreach ($actors as $actor) {
            $episode_page .= "<a href='http://haytex.epizy.com/casting/" . $actor['id'] . "' target='_blank'>" . $actor['name'] . "</a> ";
        }
        $episode_page = rtrim($episode_page, ', ');
        $episode_page .= "</p>";
        $episode_page .= '<p class="Genre">
                <span>Genres:</span> ';
        foreach ($genres as $genre) {
            $episode_page .= '<a href="http://haytex.epizy.com/genre/'.$genre['name'].'" target="_blank">'.$genre['name'].'</a>, ';
        }
        $episode_page     = rtrim($episode_page, ', ');
        $episode_page .= "</p>";

$episode_page .="</div>
</div>
</header>
</article>

<!--<épisodes>-->
<?php include('$lien_page-$season_number.php')?>
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
<section>
        <div class='Top AAIco-movie_filter'>
            <div class='Title'>Vous aimerez aussi</div>
          </div>
          <div class='MovieListTop owl-carousel Serie'>
          ";
          
        $url_prop = "https://api.themoviedb.org/3/tv/$series_id/recommendations?api_key=$api_key&language=fr";
        // Effectuer la requête à l'API
$response = file_get_contents($url_prop);

// Vérifier si la requête a réussi
if ($response !== false) {
    // Convertir la réponse JSON en tableau associatif
    $recommendations = json_decode($response, true);

    // Parcourir les recommandations
    foreach ($recommendations['results'] as $recommendation) {
        $title_prop = $recommendation['name'];
        $poster_prop = $recommendation['poster_path'];
        $imageUrl_prop = "https://image.tmdb.org/t/p/w200$poster_prop";
        $isSeries = $recommendation['media_type'] === 'tv';
        // transformer $lien_page en minuscules
    $serie_url = strtolower($title_prop);

    // supprimer les accents et les ponctuations
    $serie_url = preg_replace('/[\p{P}\p{S}]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', $serie_url));

    // remplacer les espaces par des underscores
    $serie_url = str_replace(' ', '_', $serie_url);

            $episode_page .= "
        <div class='TPostMv'>
            <div class='TPost B'>
                <a href='http://haytex.epizy.com/films/" . $serie_url . "'>
                    <div class='Image'>
                        <figure class='Objf TpMvPlay AAIco-play_arrow'>
                            <img loading='lazy' class='owl-lazy' data-src='" . $imageUrl_prop . "' alt='" . $title_prop . "' />
                        </figure>
                        <span class='Qlty'>SERIE</span>
                    </div>
                    <h2 class='Title'>" . $title_prop . "</h2>
                </a>
            </div>
        </div>";
        };
}
        $episode_page .="</section>
      </div>
    </div>
    
    <footer class='Footer'>
      <div class='Bot'>
        <div class='Container'>
          <p>2022 Copyright © Haytex Tous Droits Réservés</p>
        </div>
      </div>
    </footer>
  

<div id='disqus_recommendations'><iframe id='dsq-app8627' name='dsq-app8627' allowtransparency='true' frameborder='0' scrolling='no' tabindex='0' title='Disqus' width='100%' src='https://disqus.com/recommendations/?base=default&amp;f=haytex&amp;t_u=http%3A%2F%2Fhaytex.epizy.com%2Findex%2F&amp;t_d=Haytex%20%7C%20Acceuil&amp;t_t=Haytex%20%7C%20Acceuil#version=3b8336c2a620b47aa2fac91f7787d2b1' style='width: 100% !important; border: none !important; overflow: hidden !important; height: 0px !important; display: inline !important; box-sizing: border-box !important;' horizontalscrolling='no' verticalscrolling='no'></iframe></div>
                    <script> 
(function() { // REQUIRED CONFIGURATION VARIABLE: EDIT THE SHORTNAME BELOW
var d = document, s = d.createElement('script'); // IMPORTANT: Replace EXAMPLE with your forum shortname!
s.src ='https://haytex.disqus.com/recommendations.js'; s.setAttribute('data-timestamp', +new Date());
(d.head || d.body).appendChild(s);
})();
</script>




<!--NE PAS TOUCHER-->
  <style type='text/css'> :root{ --body: #0b0c0c; --text: #bfc1c3; --link: #ffffff; --primary: 690DAB; } </style>
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
  <link rel='icon' href='http://haytex.epizy.com/Haytex_logo.png' sizes='192x192'>

  <style id='tp_style_css' type='text/css'>
[class*=fa-]:before{font-family:'Font Awesome 5 Pro';font-weight:900;-moz-osx-font-smoothing:grayscale;-webkit-font-smoothing:antialiased;display:inline-block;font-style:normal;font-variant:normal;text-rendering:auto}.far:before,.fab:before{font-weight:400}i[class*=fa-]{display:inline-block}.fab:before{font-family:'Font Awesome 5 Brands'}
  </style>
  <link rel='stylesheet' id='font-awesome-public_css-css' href='http://haytex.epizy.com/css/font-awesome.css' type='text/css' media='all'>
  <link rel='stylesheet' id='material-public-css-css' href='https://allmoviesforyou.net/wp-content/themes/toroflix/public/css/material.css?ver=1.2.0' type='text/css' media='all'>
  <script>
    (function() {
    var d = document, s = d.createElement('script');
    s.src = 'https://haytex.disqus.com/embed.js';
    s.setAttribute('data-timestamp', +new Date());
    (d.head || d.body).appendChild(s);
    })();
</script>
<script src='https://s1.bunnycdn.ru/assets/template_1/min/all.js?6379b4a8' async=''></script>
</body></html>
";

             // Créer une nouvelle page pour chaque épisode
        $filename = '../../series/'. $lien_page .'/'. $season_number ."x". $episode_number .'.php';
        $handle   = fopen($filename, 'w');
        fwrite($handle, $episode_page);
    }
$liste .= "</ul></div></section>"; 


$fiche = "<li>
   <article class='post dfx fcl movies more-info'>
   <div class='post-thumbnail or-1'>
     <figure>
       <img class='trs' src='$serie_poster_url' loading='lazy' alt='$serie_name'>
     </figure>

     <span class='play fa-play'></span>

     <span class='quality'>HD</span>
   </div>
   <a href='/series/$lien_page' class='lnk-blk'>
     <span class='sr-only'>Regarder</span>
   </a>
   <div class='post info' role='tooltip'>
     <div class='entry-header'>
       <div class='entry-title'>$serie_name</div>
      <div class='entry-meta'>
        <span class='rating fa-star'><span>$note/10</span></span><span class='year'>$date</span><span class='duration'>$serie_total_seasons saisons</span><span class='quality'>HD</span>
      </div>
    </div>
    <div class='entry-content'>
      <p>
       $serie_overview
      </p>
    </div>
    <div class='details-lst'>
      <p class='rw sm'>";          
//Réalisateurs
        foreach ($crew as $member) {
            if ($member['job'] == 'Director') {
                $directors[] = $member['name'];
            }
        }
        if (!empty($directors)) {
            $fiche .= "<p class='Director'><span>Réalisation:</span> ";
            foreach ($directors as $director) {
                $fiche .= "<a href='http://haytex.epizy.com/director/" . urlencode($director) . "' target='_blank'>" . $director . "</a>, ";
            }
            $fiche = rtrim($fiche, ', ');
            $fiche .= "</p>";
        }
        //Acteurs
        $fiche .= "<p class='Cast Cast-sh oh'>
        <span>Casting principal:</span> ";
        $count = 0;
    foreach ($actors as $actor) {
        if ($count >= 3) {
            break;
        }
        $fiche .= "<a href='http://haytex.epizy.com/casting/" . $actor['id'] . "' target='_blank'>" . $actor['name'] . "</a> ";
        $count++;
    }
    if (count($actors) > 3) {
        $fiche .= "...";
    };
        $fiche = rtrim($fiche, ', ');
        $fiche .= "</p>";
        $fiche .= '<p class="Genre">
                <span>Genres:</span> ';
        foreach ($genres as $genre) {
            $fiche .= "<a href='http://haytex.epizy.com/genre/".$genre['name']."' target='_blank'>".$genre['name']."</a>, ";
        }
        $fiche     = rtrim($fiche, ', ');
        $fiche .= "</p>";
                  $fiche .= "</div>
    <div class='rw sm'>
      <bouton class='fg1'>
        <a href='/series/". $lien_page ."' class='btn blk watch-btn sm fa-play-circle'>Regarder la série</a>
      </bouton>
    </div>
    <div class='post-thumbnail'>
      <figure>
        <img class='trs' src='". $serie_poster_url ."' loading='lazy' alt='$serie_name'>
      </figure>
    </div>
  </div>
</article>
</li>
";

// Enregistrer la page HTML dans un fichier
    $filenom = '../../php/series/' . $lien_page . '.php';
    file_put_contents($filenom, $fiche);
    
    // Inclure le fichier contenant le tableau de films
    require_once('../../php/series/index.php');

        // Vérifier si le film existe déjà dans le tableau
if (!in_array($lien_page . ".php", $series)) {
    // Ajouter le nouveau film au tableau de films
    array_push($series, $lien_page . ".php");

    // Trier le tableau de films
    asort($series);

    // Réécrire le fichier contenant le tableau de films avec les nouvelles données
    file_put_contents('../../php/series/index.php', '<?php $series = ' . var_export($series, true) . ';');
};

// créer une nouvelle page pour chaque épisode
                $filenom =  '../../php/series/episodes/' .$lien_page. "-". $season_number .'.php';
                $handle   = fopen($filenom, 'w');
                fwrite($handle, $liste);

                // fermer le fichier
                fclose($handle);


   echo '<h1>Créer les épisodes de la saison '. $season_number .' de la série '. $serie_name .' ? Vous devrez ajouter les liens manuellement.</h1>';
                echo '<input type="submit"  name="confirm" value="Confirmer la création de la saison" style="text-align: center;"/>';
    echo '</form>';
//-----------------------------------------------------------PAGE PRINCIPALE----------------------------------------------------------------------//




// Replace with your Discord webhook URL
$webhookUrl = "https://discord.com/api/webhooks/1130116660263661658/KDm-MecxdH1N5RIXdzVAR1JYsqZBj-ZJOzqKqjThXH8qEJVBxDiJFeEOkFLoWuWAkiMj" ;

// Inclure le fichier contenant le tableau de séries
require_once('../../php/series/index.php');

// Vérifier si la série existe déjà dans le tableau

// Construct the payload data
$payload = [
  "content" => "<@&1075053973846884442>",
  "embeds" => [
    [
      "title" => "La saison ". $season_number ." de la série " . $serie_name . " vient d'être ajoutée au site, les liens des embed ainsi que les modes de liaisons de page seront corrigées d'ici peu",
      "thumbnail" => [
        "url" => $serie_poster_url
      ],
      "fields" => [
        [
          "name" => "Nombre total de saisons",
          "value" => $serie_total_seasons
        ],
        [
          "name" => "Nombre total d'épisodes",
          "value" => $serie_total_episodes
        ],
        [
          "name" => "Note",
          "value" => $note . "/10"
        ],
        [
          "name" => "Réalisation",
          "value" => implode(", ", $directors)
        ],
        [
          "name" => "Casting principal",
          "value" => implode(", ", array_column(array_slice($actors, 0, 5), 'name'))
        ],
        [
          "name" => "Genres",
          "value" => implode(", ", array_column($genres, 'name'))
        ],
        [
          "name" => "Lien de la série",
          "value" => $url_serie
        ],
      ],
      "image" => [
        "url" => $serie_backdrop_url
      ],
      "color" => 0x00FF00,
    ]
  ],
];

// Send the payload data to the Discord webhook
$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);


            } else {
                echo "Aucun épisode trouvé pour cette série.";
            }
        } else {
            echo '<form method="post">';
            echo '<label for="series_id">ID de la série :</label>';
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
