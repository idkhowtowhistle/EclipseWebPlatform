<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<div class="container">
   <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
         <li data-target="#carouselExampleCaptions" data-slide-to="0" class="active"></li>
         <li data-target="#carouselExampleCaptions" data-slide-to="1"></li>
         <li data-target="#carouselExampleCaptions" data-slide-to="2"></li>
      </ol>
      <div class="carousel-inner">
         <div class="carousel-item active">
            <img src="/img/Helpimg1.png" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block" style="color:black">
               <h5>Installing the Client</h5>
               <p>Refer to step 1.</p>
            </div>
         </div>
         <div class="carousel-item">
            <img src="/img/Splash.png" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block" style="color:black">
               <h5>Second slide label</h5>
               <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
         </div>
         <div class="carousel-item">
            <img src="/img/Splash.png" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block" style="color:black">
               <h5>Third slide label</h5>
               <p>Praesent commodo cursus magna, vel scelerisque nisl consectetur.</p>
            </div>
         </div>
      </div>
      <a class="carousel-control-prev" style="background-color:black" href="#carouselExampleCaptions" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" style="background-color:black" href="#carouselExampleCaptions" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
      </a>
   </div>
   <hr>
   <h3 class="font-weight-normal">Hosting a server</h3>
   <p>Hosting a server should be pretty easy to set up with this tutorial!</p>
   <ol>
      <li>
         Installing the client if you haven't
      </li>
      <li>
         Getting your IP address
      </li>
      <li>
         Port forwarding
      </li>
      <li>
         Starting a server
      </li>
      <li>
         Creating a game on-site
      </li>
   </ol>
   <p>There will be images in the carousel to accompany the tutorial, so don't worry about visual problems. If you cannot see the image properly, right click and open the image in a new tab.</p>
   <h3 class="font-weight-normal">Step 1 - Installation (Slide 1)</h3>
   <p>To install our client, go ahead and go to the <a href="/games">games page</a> and click "Download Client" (fig 1). </p>
   <p>That downloads the <?= SITE_CONFIG["site"]["name"] ?> launcher. Run it once and it will install the full 2013 client in your local AppData folder and register its protocol (fig 2/3).</p>
   <p>After that, the "Play" button on any server page opens the game straight from your browser.</p>
   <h3 class="font-weight-normal">Step 2 - Getting your IP Address</h3>
   <p>You need to know your own IP address to allow users to connect to your server. There are a multitude of websites that allow you to get your IP address such as <a href="https://whatismyipaddress.com/">whatismyipaddress.com</a>. Alternatively you can just Google "my ip address" and it should give you the IP.</p>
   <p>A reminder that your IP address needs to be a IPv4 type, so make sure there are 3 dots seperating the numbers. (xxx.xxx.xxx.xxx)</p>
   <h3 class="font-weight-normal">Step 3 - Port Forwarding</h3>
   <p>Now that you have your IP address, it is time to open your gameserver port so any user may join your server. Unfortunately, as people have different ISPs, the experience may be different.</p>
   <p>Here are some links to port forwarding which may guide you on how to port forward. <a href="https://en.wikipedia.org/wiki/Port_forwarding">Wikipedia</a> <a href="https://portforward.com/">portforward.com <a href="https://www.wikihow.com/Set-Up-Port-Forwarding-on-a-Router">WikiHow</a></a></p>
   <p>We recommend you port forward 53640 as it is the most common port that the client uses. If it is allowed you should also use TCP/UDP (both)</p>
   <h3 class="font-weight-normal">Step 4 - Start a server (Slide 2)</h3>
   <p>N/A</p>
   <h3 class="font-weight-normal">Step 5 - Create an on-site game (Slide 3)</h3>
   <p>N/A</p>
   <hr>
   <p>Thank you for reading this tutorial. If you require more help please contact one of the admins.</p>
</div>
<?php pageBuilder::buildFooter();?>