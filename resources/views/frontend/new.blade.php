<!DOCTYPE html>
<html lang="en">
<head>
    <title>BIOSPEX | Lead Public Digitization Expeditions</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="FSU Department of Biological Science">
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,600,700|Work+Sans" rel="stylesheet">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
    <!-- BIOSPEX styles -->
    <link href="css/main.css" rel="stylesheet">
    <link href="https://github.com/jonsuh/hamburgers/blob/master/dist/hamburgers.css" rel="stylesheet">
</head>

<body>


<header>
    <nav class="header navbar navbar-expand-md box-shadow">
        <a href="/"><img src="img/biospex_logo.svg" alt="BIOSPEX" class="my-0 mr-md-auto top-logo font-weight-normal" /></a>

        <div class="three col d-block d-sm-block d-md-none" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <div class="hamburger" id="hamburger-9">
                <span class="line"></span>
                <span class="line"></span>
                <span class="line"></span>
            </div>
        </div>

        <div class="collapse navbar-collapse text-capitalize" id="navbarsExampleDefault">

            <ul class="navbar-nav ml-auto">

                <li class="nav-item mr-2 dropdown">
                    <a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">about</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown01">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </li>

                <li class="nav-item active">
                    <a class="nav-link mr-2" href="#">projects <span class="sr-only">(current)</span></a>
                </li>

                <li class="nav-item mr-2">
                    <a class="nav-link" href="#">expeditions</a>
                </li>

                <li class="nav-item mr-2">
                    <a class="nav-link" href="#">events</a>
                </li>

                <li class="nav-item mr-2">
                    <a class="nav-link" href="#">contact</a>
                </li>
            </ul>

            <button class="btn btn-danger pl-4 pr-4" type="submit">ADMIN</button>

        </div>
    </nav>
</header>


<!-- Modal -->
<div class="modal fade fade bd-example-modal-lg" id="ModalCenter" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div><i class="far fa-share-square"></i> Share</div>
                <div><h2 class="color-action">SCOREBOARD</h2></div>
                <div><button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="far fa-times-circle"></i></span>
                    </button>
                </div>
            </div>

            <div class="modal-body text-center" style="background-color:#e83f29;padding-top:35px;">
                <h2 class="text-white text modal-number">1,034<br>
                    <small>Transcriptions</small></h2>
            </div>

            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Transcriptions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>We Dig Digging Plants</td>
                    <td>498</td>
                </tr>
                <tr>
                    <th scope="row">2</th>
                    <td>Arizona State Cardinals</td>
                    <td>455</td>
                </tr>
                <tr>
                    <th scope="row">3</th>
                    <td>A Bird in the Bush</td>
                    <td>4</td>
                </tr>
                </tbody>
            </table>

            <!-- countdown clock -->
            <h2 class="text-center color-action pt-4">Time Remaining</h2>
            <div id="clockdiv">
                <div>
                    <span class="days"></span>
                    <div class="smalltext">Days</div>
                </div>
                <div>
                    <span class="hours"></span>
                    <div class="smalltext">Hours</div>
                </div>
                <div>
                    <span class="minutes"></span>
                    <div class="smalltext">Minutes</div>
                </div>
                <div>
                    <span class="seconds"></span>
                    <div class="smalltext">Seconds</div>
                </div>
            </div>


            <div class="modal-footer text-center">
                <button type="button" class="btn btn-outline-primary color-actio align-self-center" data-dismiss="modal">EXIT</button>
            </div>
        </div>
    </div>
</div>
<!-- end modal -->





<div style="background-color: #ededed;">
    <div class="container">

        <!-- Grid row-->
        <div class="row py-3 align-items-center">


            <!-- Grid column -->
            <div class="col-md-12 text-center d-inline d-sm-flex align-items-start justify-content-between">

                <!-- Facebook -->
                <a class="figure-img"><img src="img/contributors/fsu.png" alt="FSU"></a>

                <!-- Twitter -->
                <a class="figure-img"><img src="img/contributors/flmnh.png" alt="FSU"></a>

                <!--Linkedin -->
                <a class="figure-img"><img src="img/contributors/idigbio.png" alt="FSU" style="max-width:200px; margin-top:-10px;"></a>

                <!--Instagram-->
                <a class="figure-img"><img src="img/contributors/uf.png" alt="FSU"></a>

                <a class="figure-img"><img src="img/contributors/nsf.png" alt="FSU"></a>
            </div>
            <!-- Grid column -->
        </div>
        <!-- Grid row-->
    </div>
</div>




<main role="main">

    <section class="jumbotron text-center">
        <div class="container">
            <h1>BIOSPEX Expeditions</h1>
            <p>BIOSPEX Expeditions are your way to contribute to the digitization of collections all over the world</p>
            <p>
                <a href="#" class="btn btn-primary my-2">Sign Up</a>
            </p>
        </div>
    </section>



    <article class="album py-5 bg-light">

        <div class="container">

            <div class="row">

                <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card pt-2 mb-4 box-shadow">

                        <!-- overlay -->
                        <div id="overlay">
                            <div class="overlay-text">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                                    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                            </div>
                        </div>
                        <!-- end overlay -->

                        <img class="card-img-top" src="img/card-exp-image.jpg" alt="Card image cap">
                        <a href="#" class="View-overlay"><h2 class="card-title">Expedition Title Name Here <i class="fa fa-angle-right text-white align-middle"> </i></h2></a>

                        <div class="card-body text-center">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <p><a href="#" class="color-action"><i class="fas fa-project-diagram color-action"></i> Project Name Here</a></p>
                                <p>53% Complete</p>
                            </div>

                            <div class="d-flex align-items-start justify-content-between">
                                <p><a href="#"><i class="far fa-share-square"></i> Share</a></p>
                                <p><a href="#"><i class="far fa-keyboard"></i> Participate</a></p>
                            </div>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-outline-primary mt-3 text-center" data-toggle="modal" data-target="#ModalCenter">
                                Launch Scoreboard
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card pt-2 mb-4 box-shadow">

                        <!-- overlay -->
                        <div id="overlay">
                            <div class="overlay-text">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                                    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                            </div>
                        </div>
                        <!-- end overlay -->

                        <img class="card-img-top" src="img/card-exp-image.jpg" alt="Card image cap">
                        <a href="#" class="View-overlay"><h2 class="card-title">Expedition Title Name Here <i class="fa fa-angle-right text-white align-middle"> </i></h2></a>

                        <div class="card-body text-center">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <p><a href="#" class="color-action"><i class="fas fa-project-diagram color-action"></i> Project Name Here</a></p>
                                <p>53% Complete</p>
                            </div>

                            <div class="d-flex align-items-start justify-content-between">
                                <p><a href="#"><i class="far fa-share-square"></i> Share</a></p>
                                <p><a href="#"><i class="far fa-keyboard"></i> Participate</a></p>
                            </div>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-outline-primary mt-3 text-center" data-toggle="modal" data-target="#ModalCenter">
                                Launch Scoreboard
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card pt-2 mb-4 box-shadow">

                        <!-- overlay -->
                        <div id="overlay">
                            <div class="overlay-text">
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
                                    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                            </div>
                        </div>
                        <!-- end overlay -->

                        <img class="card-img-top" src="img/card-exp-image.jpg" alt="Card image cap">
                        <a href="#" class="View-overlay"><h2 class="card-title">Expedition Title Name Here <i class="fa fa-angle-right text-white align-middle"> </i></h2></a>

                        <div class="card-body text-center">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <p><a href="#" class="color-action"><i class="fas fa-project-diagram color-action"></i> Project Name Here</a></p>
                                <p>53% Complete</p>
                            </div>

                            <div class="d-flex align-items-start justify-content-between">
                                <p><a href="#"><i class="far fa-share-square"></i> Share</a></p>
                                <p><a href="#"><i class="far fa-keyboard"></i> Participate</a></p>
                            </div>
                            <!-- Button trigger modal -->
                            <button type="button" class="btn btn-outline-primary mt-3 text-center" data-toggle="modal" data-target="#ModalCenter">
                                Launch Scoreboard
                            </button>
                        </div>
                    </div>
                </div>

            </div><!-- row -->


            <div class="row">
                <div class="col-12 col-md-10 offset-md-1">
                    <div class="card white box-shadow py-5 my-5 p-sm-5">


                        <h1 class="text-center"><img src="img/we-dig-fl-plants.svg" class="img-fluid mb-3" style="max-width: 850px;" alt="We Dig FL Plants"></h1>

                        <div class="col-12">

                            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                                <a href="#"><i class="fas fa-binoculars fa-2x"></i> <span class="d-none text d-sm-inline">Expeditions</span></a>
                                <a href="#"><i class="far fa-calendar-times fa-2x"></i> <span class="d-none text d-sm-inline">Events</span></a>
                                <a href="#"><i class="fab fa-twitter fa-2x"></i> <span class="d-none text d-sm-inline">Follow</span></a>
                                <a href="#"><i class="far fa-envelope fa-2x"></i> <span class="d-none text d-sm-inline">Contact</span></a>
                            </div>
                        </div>

                        <hr class="pt-0 pb-4">

                        <div class="col-12 col-md-9 offset-md-2">
                            <h3>Contact</h3>
                            <p>
                                <a href="mailto:amast@edu.net" class="text">Prof. Austin Mast</a>
                            </p>

                            <h3>Partners</h3>
                            <p>
                                <a href="mailto:amast@edu.net" class="text">Lorem Native Plant</a><br>
                                <a href="mailto:amast@edu.net" class="text">Ipsum Native Plant Society</a><br>
                                <a href="mailto:amast@edu.net" class="text">Bacon Native</a><br>
                                <a href="mailto:amast@edu.net" class="text">FSU Plant Society</a><br>
                                <a href="mailto:amast@edu.net" class="text">Society 999</a><br>
                            </p>

                            <h3>Funding Sources</h3>
                            <p>
                                <a href="mailto:amast@edu.net" class="text">Lorem Native Plant</a><br>
                                <a href="mailto:amast@edu.net" class="text">Ipsum Native Plant Society</a><br>
                                <a href="mailto:amast@edu.net" class="text">Bacon Native</a><br>
                                <a href="mailto:amast@edu.net" class="text">FSU Plant Society</a><br>
                                <a href="mailto:amast@edu.net" class="text">Society 999</a><br>
                            </p>

                            <h3>Funding Sources</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                                quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
                                cillum dolore eu fugiat nulla pariatur.</p>

                            <h3>Scope</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua.</p>

                            <h3>Activities</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua.</p>

                            <h3>Resources</h3>
                            <p>We Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                                tempor incididunt ut labore et dolore magna aliqua.</p>
                        </div>




                        <div class="d-flex align-items-start justify-content-between">
                            <a href="#"><i class="far fa-share-square"></i> Share</a>
                            <a href="#"><i class="far fa-keyboard"></i> Participate</a>
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&bg=55595c&fg=eceeef&text=Thumbnail" alt="Card image cap">
                        <div class="card-body">
                            <p class="card-text">Secondary Card to be styled</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                                </div>
                                <small class="text-muted">lorem</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&bg=55595c&fg=eceeef&text=Thumbnail" alt="Card image cap">
                        <div class="card-body">
                            <p class="card-text">Secondary Card to be styled</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                                </div>
                                <small class="text-muted">lorem</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <img class="card-img-top" data-src="holder.js/100px225?theme=thumb&bg=55595c&fg=eceeef&text=Thumbnail" alt="Card image cap">
                        <div class="card-body">
                            <p class="card-text">Secondary Card to be styled</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                                </div>
                                <small class="text-muted">lorem</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- row -->
        </div> <!-- container -->
    </article> <!-- bglight -->
</main>



<!--
sub footer -->
<aside style="background-color: #ededed;">
    <div class="container">

        <!-- Grid row-->
        <div class="row py-3 align-items-center">


            <!-- Grid column -->
            <div class="col-md-10 col-md-offset-1 text-center d-inline d-sm-flex align-items-start justify-content-between">

                <h3>Get Connected</h3>

                <!-- Facebook -->
                <a class="figure-img"><i class="fab fa-twitter fa-4x"></i></a>

                <!-- Twitter -->
                <a class="figure-img"><i class="fab fa-instagram fa-4x"></i></a>

                <!--Linkedin -->
                <a class="figure-img"><i class="fab fa-facebook fa-4x"></i></a>

                <!--Instagram-->
                <a class="figure-img"><i class="fas fa-envelope fa-4x"></i></a>
            </div>
            <!-- Grid column -->
        </div>
        <!-- Grid row-->
    </div>
</aside>

<!-- Footer -->
<footer class="page-footer font-small blue-grey lighten-5">
    <!-- Footer Links -->
    <div class="container text-center text-md-left mt-5">

        <!-- Grid row -->
        <div class="row mt-3 dark-grey-text">

            <!-- Grid column -->
            <div class="col-md-3 col-xl-3 mb-4">

                <!-- Content -->
                <h6><img src="img/biospex_logo.svg" alt="BIOSPEX"></h6>
                <p class="small text-justify pt-2">is funded by a grant from the National Science Foundation’s Advances in Biological Informatics Program (Award Number 1458550). iDigBio is funded by a grant from the National Science Foundation's Advancing Digitization of Biodiversity Collections Program (Cooperative Agreement EF-1115210). Any opinions, findings, and conclusions or recommendations expressed in this material are those of the author(s) and do not necessarily reflect the views of the National Science Foundation.</p>
            </div>
            <!-- Grid column -->

            <div class="col-sm-1">
            </div>

            <!-- Grid column -->
            <div class="col-md-2 col-12 mx-auto mb-4">

                <!-- Links -->
                <h6 class="text-uppercase font-weight-bold">About</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">
                <p>
                    <a class="dark-grey-text" href="#!">Team</a>
                </p>
                <p>
                    <a class="dark-grey-text" href="#!">Inquire</a>
                </p>
                <p>
                    <a class="dark-grey-text" href="#!">FAQ</a>
                </p>
                <p>
                    <a class="dark-grey-text" href="#!">contact</a>
                </p>

            </div>
            <!-- Grid column -->


            <!-- Grid column -->
            <div class="col-md-2  mx-auto mb-4">
                <h6 class="text-uppercase font-weight-bold">Resources</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">

                <p>
                    <a class="dark-grey-text" href="#!">Your Account</a>
                </p>
                <p>
                    <a class="dark-grey-text" href="#!">Events</a>
                </p>
                <p>
                    <a class="dark-grey-text" href="#!">Expeditions</a>
                </p>
                <p>
                    <a class="dark-grey-text" href="#!">Help</a>
                </p>
            </div>
            <!-- Grid column -->



            <!-- Grid column -->
            <div class="col-md-2 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">

                <!-- Links -->
                <h6 class="text-uppercase font-weight-bold">Contact</h6>
                <hr class="white mb-2 mt-2 d-inline-block mx-auto" style="width:60px;">
                <p>
                    Tallahassee, FL 32301, USA</p>
                <p>
                    info@fsu.com</p>
                <p>
                    + 01 850 567 88</p>
                <p>
                    + 01 850 567 89</p>

            </div>
            <!-- Grid column -->

        </div>
        <!-- Grid row -->

    </div>
    <!-- Footer Links -->

    <!-- Copyright -->
    <div class="footer-copyright text-center text-black-50 py-3">© 2019 Copyright
        <a class="dark-grey-text" href="#"> FSU Deptartment of Biological Science</a>
    </div>
    <!-- Copyright -->

</footer>
<!-- Footer -->

<!-- Bootstrap core JavaScript ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js" integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
<script src="js/holder.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $(".hamburger").click(function(){
            $(this).toggleClass("is-active");
        });
    });
</script>

<!--
        countdown script -->
<script type="text/javascript">
    function getTimeRemaining(endtime) {
        var t = Date.parse(endtime) - Date.parse(new Date());
        var seconds = Math.floor((t / 1000) % 60);
        var minutes = Math.floor((t / 1000 / 60) % 60);
        var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
        var days = Math.floor(t / (1000 * 60 * 60 * 24));
        return {
            'total': t,
            'days': days,
            'hours': hours,
            'minutes': minutes,
            'seconds': seconds
        };
    }

    function initializeClock(id, endtime) {
        var clock = document.getElementById(id);
        var daysSpan = clock.querySelector('.days');
        var hoursSpan = clock.querySelector('.hours');
        var minutesSpan = clock.querySelector('.minutes');
        var secondsSpan = clock.querySelector('.seconds');

        function updateClock() {
            var t = getTimeRemaining(endtime);

            daysSpan.innerHTML = t.days;
            hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
            minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
            secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

            if (t.total <= 0) {
                clearInterval(timeinterval);
            }
        }

        updateClock();
        var timeinterval = setInterval(updateClock, 1000);
    }

    var deadline = new Date(Date.parse(new Date()) + 15 * 24 * 60 * 60 * 1000);
    initializeClock('clockdiv', deadline);
</script>
</body>
</html>