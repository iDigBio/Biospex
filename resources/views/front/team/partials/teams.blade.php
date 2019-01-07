<div class="col-md-4 text-center">
    <div class="card-project mb-4 px-4 box-shadow h-100" style="max-width: 25rem;">
        <h2 class="text-center pt-4"><a href="mailto:{{ $team->email }}">{{ $team->present()->full_name }}</a></h2>
        <hr>
        <p class="text-center"><strong>{{ $team->title }}</strong><br>
            {{ $team->department }}<br>
        <h3 class="pb-3"><a href="#">{{ $team->institution }}</a></h3>

        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                <a href="mailto:{!! $team->email !!}"><i class="far fa-envelope"></i> <span
                            class="d-none text d-sm-inline"></span></a>
                <!--
                <a href="#"><i class="fab fa-linkedin"></i> <span class="d-none text d-sm-inline"></span></a>
                <a href="#"><i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>
                <a href="tel:+18506451500"><i class="fas fa-phone-square"></i> <span
                            class="d-none text d-sm-inline"></span></a>
                -->
            </div>
        </div>
    </div>
</div>
