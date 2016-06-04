<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        @if ( ! Auth::guest())
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="{{asset('/adminlte/img/user-default-160x160.png')}}" class="img-circle" alt="User Image"/>
                </div>
                <div class="pull-left info">
                    <p>{{ $user->profile->full_name }}</p>
                    <!-- Status -->
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
        @endif

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header">NAVIGATION</li>
            <!-- Optionally, you can add icons to the links -->
            <li>
                {!! Html::linkWithIcon(route('admin.dashboard.index'), 'Dashboard', [], ['before' => 'fa fa-dashboard']) !!}
            </li>
            <li>
                {!! Html::linkWithIcon(route('admin.ocr.index'), 'OCR', [], ['before' => 'fa fa-file-image-o']) !!}
            </li>

            <li class="treeview">
                {!! Html::linkWithIcon('#', 'FAQ', [], ['before' => 'fa fa-server', 'after' => 'fa fa-angle-left pull-right']) !!}
                <ul class="treeview-menu" role="directory" style="display: none;">
                    <li class="{!! Html::active('admin.faq.index') !!}">{!! Html::linkWithIcon(route('admin.faq.index'), 'Show FAQs', ['role' => 'test'], ['before' => 'fa fa-circle-o']) !!}</li>
                    <li>{!! Html::linkWithIcon(route('admin.faq.create'), 'Create FAQ', [], ['before' => 'fa fa-circle-o']) !!}</li>
                </ul>
            </li>

            <li class="treeview">
                {!! Html::linkWithIcon('#', 'Server', [], ['before' => 'fa fa-server', 'after' => 'fa fa-angle-left pull-right']) !!}
                <ul class="treeview-menu" style="display: none;">
                    <li>{!! Html::linkWithIcon(route('admin.server.show'), 'PHP Info', [], ['before' => 'fa fa-circle-o']) !!}</li>
                </ul>
            </li>
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
