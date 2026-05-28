<!DOCTYPE html>
<html lang="en">

<head>
    <title>TashiCell Online PMS</title>
    <!-- HTML5 Shim and Respond.js IE9 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <!-- Favicon icon -->
    <link rel="shortcut icon" href="{{asset("assets/images/favicon.png")}}" type="image/x-icon">
    <link rel="icon" href="{{asset("assets/images/favicon.ico")}}" type="image/x-icon">

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Mallanna|Niconne" rel="stylesheet">

    <!-- iconfont -->
    <link rel="stylesheet" type="text/css" href="{{asset("assets/icon/icofont/css/icofont.css")}}">

    <!-- simple line icon -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.0/css/simple-line-icons.css">
{{--    <link rel="stylesheet" type="text/css" href="{{asset("assets/css/font-awesome.min.css")}}">--}}
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css">

    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="{{asset("assets/plugins/bootstrap/css/bootstrap.min.css")}}">

    <!-- Weather css -->

    <!-- Froala css -->
    <link href="{{asset('assets/plugins/froala-editor/css/codemirror.min.css')}}"/>
{{--    <link href="{{asset('assets/plugins/froala-editor/css/froala_editor.pkgd.min.css')}}" rel="stylesheet" type="text/css" />--}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/3.0.0-rc.1/css/froala_editor.pkgd.min.css" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/plugins/froala-editor/css/froala_style.min.css')}}" rel="stylesheet" type="text/css" />

    <!-- Echart js -->
{{--    <script src="{{asset("assets/plugins/charts/echarts/js/echarts-all.js")}}"></script>--}}
    <link rel="stylesheet" type="text/css" href="{{asset("assets/plugins/jquery-ui/jquery-ui.css")}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset("assets/plugins/tabulator/css/tabulator.css")}}"/>
{{--    <link rel="stylesheet" type="text/css" href="{{asset("assets/plugins/chartjs/Chart.min.css")}}"/>--}}
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css"/>
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{asset("assets/poppins/poppins.css")}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset("assets/poppins/poppinsbold.css")}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset("assets/css/main.css?ver=".randomString().randomString())}}">

    <!-- Responsive.css-->
    <link rel="stylesheet" type="text/css" href="{{asset("assets/css/responsive.css")}}">

    <!--color css-->
    <link rel="stylesheet" type="text/css" id="color" href="{{asset("assets/css/color/color-1.min.css")}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset("assets/jqueryconfirm/jquery-confirm.min.css")}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset("assets/plugins/select2/dist/css/select2.css")}}"/>

    @yield('pagestyles')
</head>

<body class="sidebar-mini fixed" data-baseurl="{{URL::to('/')}}">
<div class="loader-bg">
    <div class="loader-bar">
    </div>
</div>
<div id="body-wrapper" class="wrapper">
    <!--   <div class="loader-bg">
    <div class="loader-bar">
    </div>
</div> -->
    <!-- Navbar-->
    @if($currentRoute != 'pmscomparisionemployeesiframe')
    <header class="main-header-top hidden-print">
        <a href="#" class="logo">
            <img class="img-fluid able-logo" src="{{asset("assets/images/logo.png")}}" style="height:45px;">
            <span style="color:#fff;" class="d-block d-sm-none">TashiCell Online PMS</span>
        </a>
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button--><a href="#!" data-toggle="offcanvas" class="sidebar-toggle"></a>
            <!-- Navbar Right Menu-->
            <div class="navbar-custom-menu f-right">
                <ul class="top-nav">
                    <!--Notification Menu-->
                {{--<li class="dropdown notification-menu">--}}
                {{--<a href="#!" data-toggle="dropdown" aria-expanded="false" class="dropdown-toggle">--}}
                {{--<i class="icon-bell"></i>--}}
                {{--<span class="badge badge-danger header-badge">9</span>--}}
                {{--</a>--}}
                {{--<ul class="dropdown-menu">--}}
                {{--<li class="not-head">You have <b class="text-primary">4</b> new notifications.</li>--}}
                {{--<li class="bell-notification">--}}
                {{--<a href="javascript:;" class="media">--}}
                {{--<span class="media-left media-icon">--}}
                {{--<img class="img-circle" src="{{asset("assets/images/avatar-1.png")}}" alt="User Image">--}}
                {{--</span>--}}
                {{--<div class="media-body"><span class="block">Lisa sent you a mail</span><span class="text-muted block-time">2min ago</span></div></a>--}}
                {{--</li>--}}
                {{--<li class="bell-notification">--}}
                {{--<a href="javascript:;" class="media">--}}
                {{--<span class="media-left media-icon">--}}
                {{--<img class="img-circle" src="assets/images/avatar-2.png" alt="User Image">--}}
                {{--</span>--}}
                {{--<div class="media-body"><span class="block">Server Not Working</span><span class="text-muted block-time">20min ago</span></div></a>--}}
                {{--</li>--}}
                {{--<li class="bell-notification">--}}
                {{--<a href="javascript:;" class="media"><span class="media-left media-icon">--}}
                {{--<img class="img-circle" src="assets/images/avatar-3.png" alt="User Image">--}}
                {{--</span>--}}
                {{--<div class="media-body"><span class="block">Transaction xyz complete</span><span class="text-muted block-time">3 hours ago</span></div></a>--}}
                {{--</li>--}}
                {{--<li class="not-footer">--}}
                {{--<a href="#!">See all notifications.</a>--}}
                {{--</li>--}}
                {{--</ul>--}}
                {{--</li>--}}
                <!-- User Menu-->
                    <li class="dropdown">
                        <a href="#" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle drop icon-circle drop-image">
                            {{--                            <span><img class="img-circle" src="{{asset("assets/images/avatar-1.png")}}" style="width:40px;" alt="User Image"></span>--}}
                            <a data-toggle="tooltip" title="View PMS Manual" href="{{asset('fileuploads/Documentation/Online_PMS_Documentation.pdf?ver=8thJuly')}}" target="_blank"><i class="icon icon-book-open"></i> </a><span><strong><a style="color:white; font-weight:bold;margin-right:5px;" href="@if(Auth::user()->RoleId != 1){{URL::to('viewprofile')}}@else{{"#"}}@endif">{{Auth::user()->Name}}</a></strong> {{--<i class=" icofont icofont-simple-down"></i>--}}</span>
                        </a>
                    </li>
                    {{-- <strong><a style="color:#fff;" href="{{URL::to("changepassword")}}"><i class="fa fa-key"></i> Change Password </a></strong>--}}
                    <li class="dropdown">
			            <strong><a style="color:#fff;margin-left:10px;" href="{{URL::to("logoutandredirect")}}" ><i class="fa fa-home"></i> Home</a></strong
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    @endif
    <!-- Side-Nav-->
    @if($currentRoute != 'pmscomparisionemployeesiframe')
    <aside class="main-sidebar hidden-print " >
        <section class="sidebar" id="sidebar-scroll">

        {{--<div class="user-panel">--}}
        {{--<div class="f-left image"><br><br></div>--}}
        {{--<div class="f-left info">--}}
        {{--                    <p>{{Session::get("llportal_name")}}</p>--}}
        {{--<p>{{Auth::user()->Name}} ({{Session::get('UserDepartment')}})</p>--}}
        {{--<p class="designation">{{Auth::user()->Email}} <i class="icofont icofont-caret-down m-l-5"></i><br></p>--}}

        {{--</div>--}}
        {{--</div>--}}
        <!-- sidebar profile Menu-->
        {{--<ul class="nav sidebar-menu extra-profile-list">--}}
        {{--<li>--}}
        {{--<a class="waves-effect waves-dark" href="profile.html">--}}
        {{--<i class="icon-user"></i>--}}
        {{--<span class="menu-text">View Profile</span>--}}
        {{--<span class="selected"></span>--}}
        {{--</a>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<a class="waves-effect waves-dark" href="javascript:void(0)">--}}
        {{--<i class="icon-settings"></i>--}}
        {{--<span class="menu-text">Settings</span>--}}
        {{--<span class="selected"></span>--}}
        {{--</a>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<a class="waves-effect waves-dark" href="{{URL::to("changepassword")}}">--}}
        {{--<i class="fa fa-key"></i>--}}
        {{--<span class="menu-text">Change Password</span>--}}
        {{--<span class="selected"></span>--}}
        {{--</a>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<a class="waves-effect waves-dark" href="{{URL::to("viewprofile")}}">--}}
        {{--<i class="fa fa-user"></i>--}}
        {{--<span class="menu-text">View Profile</span>--}}
        {{--<span class="selected"></span>--}}
        {{--</a>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<a class="waves-effect waves-dark logout-confirm" href="{{URL::to("logout")}}">--}}
        {{--<i class="icon-logout"></i>--}}
        {{--<span class="menu-text">Logout</span>--}}
        {{--<span class="selected"></span>--}}
        {{--</a>--}}
        {{--</li>--}}
        {{--</ul>--}}
        <!-- Sidebar Menu-->
            <ul class="sidebar-menu">
                <li class="nav-level text-center" style="border:none; padding-bottom:0!important; font-size:16px; color: #fff;"><span style="font-size:30px; text-align: center;">Online PMS</span><!-- <br> Tashi InfoComm Limited--></li>
                <li class="nav-level text-center d-none d-sm-block" style="border:none;padding:0 0 5px 0!important;"><img src="{{asset(Auth::user()->Gender == 'M'?'img/PMS_graphic_male.png':'img/PMS_graphic_female.png')}}" width="110"/></li>
                {{--<li class="treeview @if($currentRoute=='index'){{"active"}}@endif">--}}
                {{--<a class="waves-effect waves-dark " href="{{URL::to('index')}}">--}}
                {{--<i class="fa fa-home"></i><span> Dashboard </span>--}}
                {{--</a>--}}
                {{--</li>--}}
                @if($isAdmin)
                    <li class="treeview @if(in_array($currentRoute,['departmentindex','departmentinput','sectionindex','sectioninput','designationindex','designationinput','positionindex','positioninput','criteriainput','gradestepindex','gradestepinput','employeeindex','employeeinput','hierarchyindex','hierarchyinput','disciplinaryindex','disciplinaryinput','regioncriteriaindex','regioncriteriainput','promotioncriteriaindex','promotioncriteriainput'])){{"active"}}@endif"><a class="waves-effect waves-dark" href="#!"><i class="icon-briefcase"></i><span> Manage Master Data</span><i class="icon-arrow-down"></i></a>
                        <ul class="treeview-menu">
                            <li class="treeview @if(in_array($currentRoute,['departmentindex','departmentinput'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('departmentindex')}}">
                                    <i class="fa fa-institution"></i><span> Departments </span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['sectionindex','sectioninput'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('sectionindex')}}">
                                    <i class="fa fa-bookmark"></i><span> &nbsp;Sections </span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='designationindex' || $currentRoute == 'designationinput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('designationindex')}}">
                                    <i class="fa fa-credit-card"></i><span> Designations</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['positionindex','positioninput','criteriainput'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('positionindex')}}">
                                    <i class="fa fa-tags"></i><span> Evaluation Criteria </span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['gradestepindex','gradestepinput'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('gradestepindex')}}">
                                    <i class="fa fa-align-center"></i><span> Grade / Step </span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='employeeindex' || $currentRoute == 'employeeinput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('employeeindex')}}">
                                    <i class="fa fa-users"></i><span> Employees</span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='hierarchyindex' || $currentRoute == 'hierarchyinput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('hierarchyindex')}}">
                                    <i class="fa fa-reorder"></i><span style="padding-left:3.2px;"> Appraisal Structure</span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='disciplinaryindex' || $currentRoute == 'disciplinaryinput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('disciplinaryindex')}}">
                                    <i class="fa fa-file-text-o"></i><span style="padding-left:2.9px;"> Disciplinary Records</span>
                                </a>
			    </li>
			    <li class="treeview @if($currentRoute=='regioncriteriaindex' || $currentRoute == 'regioncriteriainput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('regioncriteriaindex')}}">
                                    <i class="fa fa-building"></i><span style="padding-left:2.9px;"> Regions Criteria</span>
                                </a>
			    </li>
			    <li class="treeview @if($currentRoute=='promotioncriteriaindex' || $currentRoute=='promotioncriteriainput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('promotioncriteriaindex')}}">
                                    <i class="fa fa-tasks"></i><span style="padding-left:5.2px;"> Promotion Criteria</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview @if(in_array($currentRoute,['appraisepms','lowperformingemployeesreport','eligibleformeritoriousreport','eligibleforloareport','eligibleforregularreport','officeorderhistory','viewpmsdetails','closepms','finalizepms','generateofficeorder','officeorder','auditemployeeindex','auditemployeegoals','auditemployeepmssubmission'])){{"active"}}@endif"><a class="waves-effect waves-dark" href="#!"><i class="fa fa-file"></i><span> PMS</span><i class="icon-arrow-down"></i></a>
                        <ul class="treeview-menu">
                            <li class="treeview @if($currentRoute=='appraisepms' || $currentRoute == 'viewpmsdetails' || $currentRoute == 'finalizepms'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('appraisepms')}}">
                                    <i class="fa fa-search"></i><span> PMS Status</span>
                                </a>
			    </li>
			    <li class="treeview @if($currentRoute=='auditemployeeindex' || $currentRoute == 'auditemployeegoals' || $currentRoute == 'auditemployeepmssubmission'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('auditemployeeindex')}}">
                                    <i class="fa fa-database"></i><span> Audit PMS Submission</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['eligibleformeritoriousreport','eligibleforloareport','eligibleforregularreport'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark" href="#!"><i class="fa fa-trophy"></i><span> Eligible for Incentives</span><i class="icon-arrow-down"></i></a>
                                <ul class="treeview-menu">
                                    <li class="treeview @if($currentRoute=='eligibleformeritoriousreport'){{"active"}}@endif">
                                        <a class="waves-effect waves-dark " href="{{URL::to('eligibleformeritoriousreport')}}">
                                            <i class="fa fa-bell"></i><span>Meritorious Promotion</span>
                                        </a>
                                    </li>
                                    <li class="treeview @if($currentRoute=='eligibleforregularreport'){{"active"}}@endif">
                                        <a class="waves-effect waves-dark " href="{{URL::to('eligibleforregularreport')}}">
                                            <i class="fa fa-signing"></i><span>Regular Promotion</span>
                                        </a>
                                    </li>
                                    <li class="treeview @if($currentRoute=='eligibleforloareport'){{"active"}}@endif">
                                        <a class="waves-effect waves-dark " href="{{URL::to('eligibleforloareport')}}">
                                            <i class="fa fa-thumbs-up"></i><span>Eligible for LoA</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['lowperformingemployeesreport'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('lowperformingemployeesreport')}}">
                                    <i class="fa fa-thumbs-o-down"></i><span> Low Performing Staff </span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='generateofficeorder' || $currentRoute == 'officeorder'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('generateofficeorder')}}">
                                    <i class="fa fa-file-word-o"></i><span> Generate Office Order</span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='closepms'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('closepms')}}">
                                    <i class="fa fa-close"></i><span> Close PMS Round</span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='officeorderhistory'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('officeorderhistory')}}">
                                    <i class="fa fa-book"></i><span> Office Order History</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @if(Auth::user()->Extension == 1234)
                        <li class="treeview @if($currentRoute == 'openpms'){{"active"}}@endif">
                            <a class="waves-effect waves-dark {{--openconfirm--}}" href="{{URL::to('openpms')}}">
                                <i class="fa fa-check"></i><span> Open PMS Round</span>
                            </a>
                        </li>
                        <li class="treeview @if($currentRoute=='bugindex' || $currentRoute == 'uploadfile'){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('bugindex')}}">
                                <i class="fa fa-bug"></i><span> Error List</span>
                            </a>
                        </li>
                    @endif
                @else
                    <li class="treeview @if($currentRoute=='viewprofile' || $currentRoute == 'changepassword'){{"active"}}@endif">
                        <a class="waves-effect waves-dark " href="{{URL::to('viewprofile')}}">
                            <i class="fa fa-user"></i><span> Profile</span>
                        </a>
                    </li>
		   {{-- <li class="treeview @if(in_array($currentRoute, 
                        ['uploadtemplateinput','uploadgoalsandtaskstemplateinput','readgoalandtasktemplate',
                        'mygoalsandtargetsindex','subordinategoalsandtargetsindex','mygoalsandtargetsinput','subordinategoalsandtargetsinput',
                        'mytasktargetsindex','subordinatetasktargetsindex','mytasktargetsinput','subordinatetasktargetsinput',
                        'goalsmonitoringrevisionindex', 'viewtaskundergoal', 'mygoaltargetinput','subordinategoaltargetinput',
                        'tasksmonitoringrevisionindex','mytasktargetinput','subordinatetasktargetinput', 'fetchemployeesreport', 'goalstasksreport']
                        )){{"active"}}@endif">
                        <a class="waves-effect waves-dark" href="#!"><i class="fa fa-bullseye"></i><span> Goals & Targets</span><i class="icon-arrow-down"></i></a>
                        <ul class="treeview-menu">
                            <li class="treeview @if($currentRoute=='uploadtemplateinput'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('uploadtemplateinput')}}">
                                    <i class="fa fa-bolt"></i><span> Upload Templates </span>
                                </a>
                            </li>
                            @if(Auth::user()->PositionId != CONST_POSITION_MD)
                            <li class="treeview @if($currentRoute=='mygoalsandtargetsindex'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('mygoalsandtargetsindex')}}">
                                    <i class="fa fa-upload"></i><span> My Goals </span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='mytasktargetsindex'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('mytasktargetsindex')}}">
                                    <i class="fa fa-tasks"></i><span> My Tasks </span>
                                </a>
                            </li>
                            @endif
                            <li class="treeview @if($currentRoute=='subordinategoalsandtargetsindex'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('subordinategoalsandtargetsindex')}}">
                                    <i class="fa fa-ticket"></i><span> Subordinate Goals </span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='subordinatetasktargetsindex'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('subordinatetasktargetsindex')}}">
                                    <i class="fa fa-envelope"></i><span> Subordinate Tasks </span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='goalsmonitoringrevisionindex'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('goalsmonitoringrevisionindex')}}">
                                    <i class="fa fa-rocket"></i><span> Goals Monitoring</span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='tasksmonitoringrevisionindex'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('tasksmonitoringrevisionindex')}}">
                                    <i class="fa fa-truck"></i><span> Tasks Monitoring</span>
                                </a>
                            </li>
                            <li class="treeview @if($currentRoute=='fetchemployeesreport'){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('fetchemployeesreport')}}">
                                    <i class="fa fa-file-pdf-o"></i><span> Goals & Tasks Report</span>
				</a>
			   </li>
		       </ul>
		   </li>--}}
                    @if(Auth::user()->PositionId != CONST_POSITION_MD)
                        <li class="treeview @if($currentRoute=='submitpms'){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('submitpms')}}">
                                <i class="fa fa-edit"></i><span> Submit My PMS</span>
                            </a>
                        </li>
                        <li class="treeview @if($currentRoute=='trackpms' || $currentRoute == 'viewpmsdetails' || $currentRoute == 'resubmit'){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('trackpms')}}">
                                <i class="fa fa-eye"></i><span> Track / Resubmit PMS</span>
                            </a>
                        </li>
                        <li class="treeview @if($currentRoute=='pmshistory'){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('pmshistory')}}">
                                <i class="fa fa-book"></i><span> My PMS Evaluation History</span>
                            </a>
                        </li>

                        @if(Auth::user()->DepartmentId == 7)
                            @if(in_array(Auth::user()->PositionId,[CONST_POSITION_HOD,CONST_POSITION_HOS]))
                                <li class="treeview @if($currentRoute=='pmsgoal' || $currentRoute == 'setgoal'){{"active"}}@endif">
                                    <a class="waves-effect waves-dark " href="{{URL::to('pmsgoal')}}">
                                        <i class="fa fa-check"></i><span> Set Subordinate Goals</span>
                                    </a>
                                </li>
                            @endif
                            @if(Auth::user()->PositionId != CONST_POSITION_HOD)
                                <li class="treeview @if($currentRoute=='mypmsgoal'){{"active"}}@endif">
                                    <a class="waves-effect waves-dark " href="{{URL::to('mypmsgoal')}}">
                                        <i class="fa fa-check"></i><span> My Goals</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endif
                    @if(in_array(Auth::user()->PositionId,[CONST_POSITION_HOD,CONST_POSITION_HOS,CONST_POSITION_MD]) || $isAppraiser)
                        <li class="treeview @if($currentRoute=='empdetailsindex' || $currentRoute == 'empdetails'){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('empdetailsindex')}}">
                                <i class="fa fa-search-plus"></i><span> View Subordinates Details</span>
                            </a>
                        </li>
                        <li class="treeview @if($currentRoute=='appraisepms' || $currentRoute == 'processpms'){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('appraisepms')}}">
                                <i class="fa fa-check-circle"></i><span> Appraise Subordinates PMS</span>
                            </a>
                        </li>
                        @if(in_array(Auth::user()->PositionId,[CONST_POSITION_HOD,CONST_POSITION_MD]))
                        <li class="treeview @if(in_array($currentRoute,['eligibleformeritoriousreport','eligibleforloareport','eligibleforregularreport'])){{"active"}}@endif">
                            <a class="waves-effect waves-dark" href="#!"><i class="fa fa-trophy"></i><span> Eligible for Incentives</span><i class="icon-arrow-down"></i></a>
                            <ul class="treeview-menu">
                                <li class="treeview @if($currentRoute=='eligibleformeritoriousreport'){{"active"}}@endif">
                                    <a class="waves-effect waves-dark " href="{{URL::to('eligibleformeritoriousreport')}}">
                                        <i class="fa fa-bell"></i><span>Meritorious Promotion</span>
                                    </a>
                                </li>
                                <li class="treeview @if($currentRoute=='eligibleforregularreport'){{"active"}}@endif">
                                    <a class="waves-effect waves-dark " href="{{URL::to('eligibleforregularreport')}}">
                                        <i class="fa fa-signing"></i><span>Regular Promotion</span>
                                    </a>
                                </li>
                                <li class="treeview @if($currentRoute=='eligibleforloareport'){{"active"}}@endif">
                                    <a class="waves-effect waves-dark " href="{{URL::to('eligibleforloareport')}}">
                                        <i class="fa fa-thumbs-up"></i><span>Eligible for LoA</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview @if(in_array($currentRoute,['lowperformingemployeesreport'])){{"active"}}@endif">
                            <a class="waves-effect waves-dark " href="{{URL::to('lowperformingemployeesreport')}}">
                                <i class="fa fa-thumbs-o-down"></i><span> Low Performing Staff </span>
                            </a>
                        </li>
                        @endif
                    @endif
                @endif
                @if((Auth::user()->EmpId <> '032') && ($isAdmin || in_array(Auth::user()->PositionId,[CONST_POSITION_HOD,CONST_POSITION_MD])))
                    <li class="treeview @if(in_array($currentRoute,['audittrailreport','organizationalperformance','departmentwiseperformance','sectionwiseperformance','pmscomparisionemployees','pmsscorereport'])){{"active"}}@endif">
                        <a class="waves-effect waves-dark" href="#!"><i class="icon-graph"></i><span> Reports</span><i class="icon-arrow-down"></i></a>
                        <ul class="treeview-menu">
                            <li class="treeview @if(in_array($currentRoute,['pmsscorereport'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('pmsscorereport')}}">
                                    <span> PMS Result</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['sectionwiseperformance'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('sectionwiseperformance')}}">
                                    <span> Section Wise Performance</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['departmentwiseperformance'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('departmentwiseperformance')}}">
                                    <span> Department Wise Performance</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['organizationalperformance'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('organizationalperformance')}}">
                                    <span> Organizational Performance</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['pmscomparisionemployees'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('pmscomparisionemployees')}}">
                                    <span> PMS Score Comparision </span>
                                </a>
                            </li>
                            @if(Auth::user()->RoleId == 1 && Auth::user()->Extension == 1234)
                                <li class="treeview @if(in_array($currentRoute,['audittrailreport'])){{"active"}}@endif">
                                    <a class="waves-effect waves-dark " href="{{URL::to('audittrailreport')}}">
                                        <span> Audit Trail Report </span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if($isAdmin)
                    <li class="treeview @if(in_array($currentRoute,['filecategoryindex','filecategoryinput','fileindex','fileinput'])){{"active"}}@endif"><a class="waves-effect waves-dark" href="#!"><i class="fa fa-file-archive-o"></i><span> Manage Documents</span><i class="icon-arrow-down"></i></a>
                        <ul class="treeview-menu">
                            <li class="treeview @if(in_array($currentRoute,['filecategoryindex','filecategoryinput'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('filecategoryindex')}}">
                                    <i class="fa fa-bookmark"></i><span> Manage File Categories</span>
                                </a>
                            </li>
                            <li class="treeview @if(in_array($currentRoute,['fileindex','fileinput'])){{"active"}}@endif">
                                <a class="waves-effect waves-dark " href="{{URL::to('fileindex')}}">
                                    <i class="fa fa-file-o"></i><span> Manage Files</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li class="treeview @if($currentRoute=='files'){{"active"}}@endif">
                    <a class="waves-effect waves-dark " href="{{URL::to('files')}}">
                        <i class="fa fa-info-circle"></i><span> Guidelines and Documents</span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <!-- Sidebar chat start -->
    <div id="sidebar" class="p-fixed header-users showChat">

    </div>
    <div class="showChat_inner">
    </div>
    @endif
    <!-- Sidebar chat end-->
    <div class="content-wrapper" @if($currentRoute == 'pmscomparisionemployeesiframe')style="background:#226b86;margin-left:0;margin-top:0;"@endif>
        <!-- Container-fluid starts -->
        <!-- Main content starts -->
        @if($currentRoute != 'pmscomparisionemployeesiframe')
        <div class="container-fluid">
            <div class="row">
                <div class="main-header col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 style="color:#000;">@yield('page-header')</h6>
                        </div>
                        <div class="col-md-1">
                            @section('action-button')
                            @show
                        </div>
                    </div>
                </div>
            </div>
            @yield('content')
        </div>
        @else
            @yield('content')
        @endif
        <!-- Main content ends -->
        <!-- Container-fluid ends -->
    </div>
</div>

<div id="loader" class="hide">
    <center><i class="fa fa-spinner fa-spin fa-4x"></i></center>
</div>
<!-- Warning Section Starts -->
<!-- Older IE warning message -->
<!--[if lt IE 9]>
<div class="ie-warning">
    <h1>Warning!!</h1>
    <p>You are using an outdated version of Internet Explorer, please upgrade <br/>to any of the following web browsers to access this website.</p>
    <div class="iew-container">
        <ul class="iew-download">
            <li>
                <a href="http://www.google.com/chrome/">
                    <img src="{{asset("assets/images/browser/chrome.png")}}" alt="Chrome">
                    <div>Chrome</div>
                </a>
            </li>
            <li>
                <a href="https://www.mozilla.org/en-US/firefox/new/">
                    <img src="{{asset("assets/images/browser/firefox.png")}}" alt="Firefox">
                    <div>Firefox</div>
                </a>
            </li>
            <li>
                <a href="http://www.opera.com">
                    <img src="{{asset("assets/images/browser/opera.png")}}" alt="Opera">
                    <div>Opera</div>
                </a>
            </li>
            <li>
                <a href="https://www.apple.com/safari/">
                    <img src="{{asset("assets/images/browser/safari.png")}}" alt="Safari">
                    <div>Safari</div>
                </a>
            </li>
            <li>
                <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
                    <img src="{{asset("assets/images/browser/ie.png")}}" alt="">
                    <div>IE (9 & above)</div>
                </a>
            </li>
        </ul>
    </div>
    <p>Sorry for the inconvenience!</p>
</div>

<![endif]-->
<!-- Warning Section Ends -->

<!-- Required Jqurey -->
{{--<script src="{{asset("assets/plugins/jquery/dist/jquery.min.js")}}"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
{{--<script src="{{asset("assets/plugins/jquery-ui/jquery-ui.min.js")}}"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="{{asset('js/sticky.js')}}"></script>

{{--<script src="{{asset("assets/plugins/tether/dist/js/tether.min.js")}}"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>

<!-- Required Fremwork -->
<script src="{{asset("js/popper.js")}}"></script>
{{--<script src="{{asset("assets/plugins/bootstrap/js/bootstrap.min.js")}}"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js"></script>

<!-- waves effects.js -->
<script src="{{asset("assets/plugins/Waves/waves.min.js")}}"></script>

<!-- Scrollbar JS-->
<script src="{{asset("assets/plugins/jquery-slimscroll/jquery.slimscroll.js")}}"></script>
<script src="{{asset("assets/plugins/jquery.nicescroll/jquery.nicescroll.min.js")}}"></script>

<!--classic JS-->
<script src="{{asset("assets/plugins/classie/classie.js")}}"></script>

<!-- notification -->
<script src="{{asset("assets/plugins/notification/js/bootstrap-growl.min.js")}}"></script>

<!-- Rickshaw Chart js -->
{{--<script src="{{asset("assets/plugins/d3/d3.js")}}"></script>--}}
{{--<script src="{{asset("assets/plugins/rickshaw/rickshaw.js")}}"></script>--}}

<!-- Sparkline charts -->
{{--<script src="{{asset("assets/plugins/jquery-sparkline/dist/jquery.sparkline.js")}}"></script>--}}

<!-- Counter js  -->
<script src="{{asset("assets/plugins/waypoints/jquery.waypoints.min.js")}}"></script>
<script src="{{asset("assets/plugins/countdown/js/jquery.counterup.js")}}"></script>

<!-- custom js -->
<script type="text/javascript" src="{{asset("js/pleaserotate.min.js")}}"></script>
<script type="text/javascript" src="{{asset("assets/js/main.js")}}"></script>
<script type="text/javascript" src="{{asset("assets/pages/dashboard.js?ver=".randomString())}}"></script>
<script type="text/javascript" src="{{asset("assets/pages/elements.js")}}"></script>
{{--<script type="text/javascript" src="{{asset("assets/js/bootbox.min.js")}}"></script>--}}
{{--<script type="text/javascript" src="https://oss.sheetjs.com/js-xlsx/xlsx.full.min.js"></script>--}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.15.2/xlsx.full.min.js"></script>
<script type="text/javascript" src="{{asset("assets/plugins/tabulator/js/tabulator.min.js")}}"></script>
<script type="text/javascript" src="{{asset("assets/plugins/tabulator/js/jquery_wrapper.min.js")}}"></script>
{{--<script type="text/javascript" src="{{asset("assets/plugins/chartjs/Chart.js")}}"></script>--}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
<script type="text/javascript" src="{{asset("assets/jqueryconfirm/jquery-confirm.min.js")}}"></script>
<script src="{{asset("assets/js/menu.min.js")}}"></script>
<script src="{{asset("assets/plugins/select2/dist/js/select2.full.js")}}"></script>

<!-- FROALA EDITOR JS -->
{{--<script type="text/javascript" src="{{asset('assets/plugins/froala-editor/js/codemirror.min.js')}}"></script>--}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.3.0/codemirror.min.js"></script>
<script type="text/javascript" src="{{asset('assets/plugins/froala-editor/js/xml.min.js')}}"></script>

<!-- Include Editor JS files. -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/3.0.0-rc.1/js/froala_editor.pkgd.min.js"></script>

@yield('pagescripts')

<script src="{{asset("js/script.js?ver=".randomString())}}"></script>
@if(Session::has('successmessage'))
    <script>
        $.alert("Success! {!! Session::get('successmessage') !!}");
    </script>
@endif
@if(Session::has('errormessage'))
    <script>
        $.alert("Error! {!! Session::get('errormessage') !!}");
    </script>
@endif
<script>
    var $window = $(window);
    var nav = $('.fixed-button');
    $window.scroll(function(){
        if ($window.scrollTop() >= 200) {
            nav.addClass('active');
        }
        else {
            nav.removeClass('active');
        }
    });
</script>
<script>
    @if(Session::has('reload'))
        $.alert({
            content: "Form Session has expired. Page will reload automatically to refresh session.",
            onDestroy: function () {
                window.location.reload();
            },
        });
    @endif
</script>
</body>

</html>
