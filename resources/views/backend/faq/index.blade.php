@extends('backend.layouts.app')

@section('htmlheader_title')
    OCR
@endsection

@section('contentheader_title', 'Manage FAQs')


@section('main-content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Category 1</h3>
                    <div class="box-tools">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm">Actions</button>
                                <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('admin.faqs.create') }}">Add Question</a></li>
                                    <li><a href="#">Edit Category</a></li>
                                    <li><a href="#" class="action_confirm" data-token="{{ Session::getToken() }}"
                                           data-method="delete">Delete Category</a></li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th></th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>This is my question? Lets try expanding this a little bit</td>
                            <td>Here we might have the full answer but need to know how to view the full things
                                without
                                the table being messed up. Here we might have the full answer but need to know how
                                to view the full things without
                                the table being messed up.
                            </td>
                            <td>
                                <div class="btn-toolbar">
                                    <a href="{{ route('admin.faqs.index', [1, 1]) }}"
                                       title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                                       role="button"><span
                                                class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') -->
                                    </a>
                                    <a href="{{ route('admin.faqs.index', [1,1]) }}"
                                       title="@lang('buttons.deleteTitle')"
                                       class="btn btn-danger action_confirm btn-xs" role="button"
                                       data-token="{{ Session::getToken() }}" data-method="delete"><span
                                                class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>This is my question?</td>
                            <td>Here we might have the full answer but need to know how to view the full things
                                without
                                the table being messed up. Here we might have the full answer but need to know how
                                to view the full things without
                                the table being messed up.
                            </td>
                            <td>
                                <div class="input-group input-group-sm" style="width: 150px;">
                                    <div class="btn-toolbar">
                                        <a href="{{ route('admin.faqs.index', [1, 1]) }}"
                                           title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                                           role="button"><span
                                                    class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') -->
                                        </a>
                                        <a href="{{ route('admin.faqs.index', [1,1]) }}"
                                           title="@lang('buttons.deleteTitle')"
                                           class="btn btn-danger action_confirm btn-xs" role="button"
                                           data-token="{{ Session::getToken() }}" data-method="delete"><span
                                                    class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>This is my question?</td>
                            <td>Here we might have the full answer but need to know how to view the full things
                                without
                                the table being messed up. Here we might have the full answer but need to know how
                                to view the full things without
                                the table being messed up.
                            </td>
                            <td>
                                <div class="input-group input-group-sm" style="width: 150px;">
                                    <div class="btn-toolbar">
                                        <a href="{{ route('admin.faqs.index', [1, 1]) }}"
                                           title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                                           role="button"><span
                                                    class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') -->
                                        </a>
                                        <a href="{{ route('admin.faqs.index', [1,1]) }}"
                                           title="@lang('buttons.deleteTitle')"
                                           class="btn btn-danger action_confirm btn-xs" role="button"
                                           data-token="{{ Session::getToken() }}" data-method="delete"><span
                                                    class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>This is my question?</td>
                            <td>Here we might have the full answer but need to know how to view the full things
                                without
                                the table being messed up. Here we might have the full answer but need to know how
                                to view the full things without
                                the table being messed up.
                            </td>
                            <td>
                                <div class="input-group input-group-sm" style="width: 150px;">
                                    <div class="btn-toolbar">
                                        <a href="{{ route('admin.faqs.index', [1, 1]) }}"
                                           title="@lang('buttons.editTitle')" class="btn btn-warning btn-xs"
                                           role="button"><span
                                                    class="fa fa-wrench fa-sm"></span> <!-- @lang('buttons.edit') -->
                                        </a>
                                        <a href="{{ route('admin.faqs.index', [1,1]) }}"
                                           title="@lang('buttons.deleteTitle')"
                                           class="btn btn-danger action_confirm btn-xs" role="button"
                                           data-token="{{ Session::getToken() }}" data-method="delete"><span
                                                    class="fa fa-remove fa-sm"></span> <!-- @lang('buttons.delete') -->
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection