
    <div class="card card-project mb-4 px-4 box-shadow" style="max-width: 25rem;">
        <h2 class="text-center pt-4">{{ $team->present()->full_name }}</h2>
        <hr>
        <div class="card-body">
            <p>{{ $team->institution }}<br>
        </div>

        <div class="card-footer">
            <div class="d-flex align-items-start justify-content-between mt-4 mb-3">
                <a href="#"><i class="fab fa-linkedin"></i> <span class="d-none text d-sm-inline"></span></a>
                <a href="#"><i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>
                <a href="mailto:{!! $team->email !!}"><i class="far fa-envelope"></i> <span
                            class="d-none text d-sm-inline"></span></a>
                <a href="tel:+18506451500"><i class="fas fa-phone-square"></i> <span
                            class="d-none text d-sm-inline"></span></a>
            </div>
        </div>
    </div>
