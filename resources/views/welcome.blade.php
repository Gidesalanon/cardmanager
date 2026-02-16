<!DOCTYPE html>
<html>
<head>
<title>DONAMI CHRIST| Edition et livraison rapide de cartes d'identité scolaires</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="DONAMI, carte scolaire, identité scolaire, gestion élèves, édition cartes, établissement scolaire" />

<script type="application/x-javascript">
 addEventListener("load", function() {
   setTimeout(hideURLbar, 0);
 }, false);
 function hideURLbar(){ window.scrollTo(0,1); }
</script>

<link href="{{ asset('assets/web/css/bootstrap.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/web/css/style.css') }}" rel="stylesheet" type="text/css" />

<script src="{{ asset('assets/web/js/jquery-1.11.0.min.js') }}"></script>
<script src="{{ asset('assets/web/js/responsiveslides.min.js') }}"></script>
<script>
$(function () {
  $("#slider3").responsiveSlides({
    auto: true,
    pager: false,
    nav: false,
    speed: 500,
    namespace: "callbacks"
  });
});
</script>
</head>

<body>

<div class="header" id="home">
  <div class="container">
    <div class="logo">
      <a href="{{ route('home') }}">
        <img src="{{ asset('assets/web/images/logo-3.png') }}" alt="">
      </a>
    </div>

    <div class="menu">			
      <div class="top-menu navigation">
        <span class="menu"></span> 
        <ul class="navig">
          <li class="active"><a href="{{ route('home') }}">Accueil</a></li>
          <li><a href="#about">À propos</a></li>
          <li><a href="#services">Services</a></li>

          @guest
            <li><a href="{{ route('login') }}">Se connecter</a></li>
          @endguest

          @auth
            @if(auth()->user()->role === 'admin')
              <li><a href="{{ route('admin.dashboard') }}">Dashboard admin</a></li>
            @elseif(auth()->user()->role === 'ecole')
              <li><a href="{{ route('school.dashboard') }}">Mon espace</a></li>
            @endif
          @endauth

          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>

      <script>
        $("span.menu").click(function(){
          $("ul.navig").slideToggle("slow");
        });
      </script>

      <div class="search">
        <form>
          <input type="text" placeholder="Recherche...">
          <input type="submit" value="">
        </form>
      </div>
    </div>
    <div class="clearfix"></div>
  </div>	
</div>

<div class="banner">
  <div id="top" class="callbacks_container">
    <ul class="rslides" id="slider3">
      <li><div class="banner-bg"></div></li>
      <li><div class="banner-bg banner2"></div></li>
    </ul>
  </div>

  <div class="container">
    <div class="banner-sec">
      <div class="banner-top">

        <div class="col-md-4 banner-text">
          <div class="banner-text_grid">
            <img src="{{ asset('assets/web/images/icon1.png') }}" class="img-responsive" alt="/"/>
            <h4>Edition rapide de cartes</h4>
            <p>DONAMI CHRIST permet aux établissements scolaires d’éditer rapidement des cartes d’identités fiables et conformes.</p>
          </div>
        </div>

        <div class="col-md-4 banner-text">
          <div class="banner-text_grid">
            <img src="{{ asset('assets/web/images/icon2.png') }}" class="img-responsive" alt="/"/>
            <h4>Importation intelligente</h4>
            <p>Importer vos élèves partout où vous êtes, à partir du document officiel, et centraliser facilement leurs informations.</p>
          </div>
        </div>

        <div class="col-md-4 banner-text">
          <div class="banner-text_grid">
            <img src="{{ asset('assets/web/images/icon3.png') }}" class="img-responsive" alt="/"/>
            <h4>Livraison rapide</h4>
            <p>Les cartes produites sont livrées dans des délais courts avec un suivi administratif maîtrisé.</p>
          </div>
        </div>

        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>

<div class="welcome" id="about">
  <div class="container">
    <div class="welcome-top">
      <h1>Bienvenue sur DONAMI</h1>
      <p>
        DONAMI est une plateforme numérique dédiée aux établissements scolaires pour simplifier l’édition et la gestion des cartes d’identités des élèves.
      </p>
    </div>

    <div class="welcome-bottom">
      <div class="col-md-6 welcome-left">
        <h3>Une solution pensée pour les écoles</h3>
        <p>
          Les administrations scolaires peuvent s’inscrire, importer les documents officiels et gérer efficacement les informations élèves.
        </p>

        <div class="welcome-one">
          <div class="col-md-6 welcome-one-left">
            <img src="{{ asset('assets/web/images/w-6.jpg') }}" alt=""/>
          </div>
          <div class="col-md-6 welcome-one-right">
            <img src="{{ asset('assets/web/images/w-4.jpg') }}" alt=""/>
            <img src="{{ asset('assets/web/images/w-5.jpg') }}" class="one-top" alt=""/>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>

      <div class="col-md-6 welcome-left">
        <h3>Traitement rapide et sécurisé</h3>
        <p>
          Importer vos élèves partout où vous êtes, à partir du document officiel, et centraliser facilement leurs informations dans votre espace DONAMI.
        </p>
        <div class="welcome-one">
          <img src="{{ asset('assets/web/images/w-2.jpg') }}" alt=""/>
        </div>
      </div>

      <div class="clearfix"></div>
    </div>
  </div>
</div>

<div class="content" id="services">
  <div class="container">
    <div class="content-slogan">
      <p>
        Simplifiez vos démarches scolaires avec <a href="#">DONAMI</a>, une solution moderne pour la <a href="#">gestion</a> et l’édition rapide des cartes scolaires.
      </p>
    </div>

    <div class="slogan-sub">
      <p>
        Grâce à l’analyse automatique des documents, DONAMI permet un enregistrement massif des élèves en un seul clic.
      </p>
    </div>

    <div class="grids">
      <div class="section group">
        <div class="col-md-4 images_1_of_3">
          <img src="{{ asset('assets/web/images/g1.png') }}">
          <h3>Profil établissement</h3>
          <p>Chaque école dispose d’un espace sécurisé pour gérer ses opérations administratives.</p>
          <div class="button"><span><a href="#">Lire plus</a></span></div>
        </div>

        <div class="col-md-4 images_1_of_3">
          <img src="{{ asset('assets/web/images/g2.png') }}">
          <h3>Gestion des élèves</h3>
          <p>Les données extraites sont présentées sous forme de champs modifiables et validables.</p>
          <div class="button"><span><a href="#">Lire plus</a></span></div>
        </div>

        <div class="col-md-4 images_1_of_3">
          <img src="{{ asset('assets/web/images/g3.png') }}">
          <h3>Production & livraison</h3>
          <p>Les cartes sont générées, imprimées et livrées dans un délai optimisé.</p>
          <div class="button"><span><a href="#">Lire plus</a></span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="nature">
  <div class="container">
    <div class="nature-top">
      <h3>Une plateforme simple et efficace</h3>
      <p>
        DONAMI a été conçu pour offrir rapidité, simplicité d’utilisation et fiabilité aux établissements scolaires.
      </p>
    </div>
  </div>
</div>

<div class="fields">
  <div class="container">
    <div class="fields-top">

      <div class="col-md-4 fields-left">
        <span class="home"></span>
        <h4>Inscription simplifiée</h4>
        <p>Les services administratifs s’inscrivent facilement et accèdent à leur espace sécurisé.</p>
      </div>

      <div class="col-md-4 fields-left">
        <span class="men"></span>
        <h4>Analyse automatique</h4>
        <p>Les documents importés sont analysés pour extraire les informations essentielles.</p>
      </div>

      <div class="col-md-4 fields-left">
        <span class="pen"></span>
        <h4>Optimisation de temps réel</h4>
        <p>Moins de saisie manuelle et un enregistrement massif en un seul clic.</p>
      </div>

      <div class="clearfix"></div>
    </div>
  </div>
</div>

<div class="copy" id="contact">
  <p>Copyright © 2026 DONAMI. Tous droits réservés.</p>
</div>

</body>
</html>
