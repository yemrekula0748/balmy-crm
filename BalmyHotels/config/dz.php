<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Application Name
	|--------------------------------------------------------------------------
	|
	| This value is the name of your application. This value is used when the
	| framework needs to place the application's name in a notification or
	| any other location as required by the application or its packages.
	|
	*/

	'name' => env('APP_NAME', 'Koki Laravel'),


	'public' => [
		'global' => [
			'css' => [
				'vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
				'https://cdn.lineicons.com/2.0/LineIcons.css',
				'css/style.css',
			],
			'js' => [
				'top'=> [
					'vendor/global/global.min.js',
					'vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
				],
				'bottom'=> [
					'js/custom.min.js',
					'js/deznav-init.js',
				],
			],
		],
		'pagelevel' => [
			'css' => [
				'KokiAdminController_dashboard_1' => [
					''
				],
				'KokiAdminController_analytics' => [
					'vendor/jqvmap/css/jqvmap.min.css',
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_customer_list' => [
					'vendor/jqvmap/css/jqvmap.min.css',
					'vendor/datatables/css/jquery.dataTables.min.css',
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_order' => [
					'vendor/jqvmap/css/jqvmap.min.css',
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_order_list' => [
					'vendor/jqvmap/css/jqvmap.min.css',
					'vendor/datatables/css/jquery.dataTables.min.css',
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_review' => [
					'vendor/jqvmap/css/jqvmap.min.css',
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_koki_element' => [
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_app_calender' => [
					'vendor/fullcalendar/css/main.min.css',
				],
				'KokiAdminController_app_profile' => [
					'vendor/lightgallery/css/lightgallery.min.css'
				],
				'KokiAdminController_post_details' => [
					'vendor/lightgallery/css/lightgallery.min.css'
				],
				'KokiAdminController_chart_chartist' => [
					'vendor/chartist/css/chartist.min.css',
				],
				'KokiAdminController_chart_chartjs' => [
				],
				'KokiAdminController_chart_flot' => [
				],
				'KokiAdminController_chart_morris' => [
				],
				'KokiAdminController_chart_peity' => [
				],
				'KokiAdminController_chart_sparkline' => [
				],
				'KokiAdminController_ecom_checkout' => [
				],
				'KokiAdminController_ecom_customers' => [
				],
				'KokiAdminController_ecom_invoice' => [
				],
				'KokiAdminController_ecom_product_detail' => [
					'vendor/star-rating/star-rating-svg.css'
				],
				'KokiAdminController_ecom_product_grid' => [
				],
				'KokiAdminController_ecom_product_list' => [
					'vendor/star-rating/star-rating-svg.css'
				],
				'KokiAdminController_ecom_product_order' => [
				],
				'KokiAdminController_email_compose' => [
					'vendor/dropzone/dist/dropzone.css',
				],
				'KokiAdminController_email_inbox' => [
					''
				],
				'KokiAdminController_email_read' => [
					''
				],
				'KokiAdminController_form_ckeditor' => [
					'',
				],
				'KokiAdminController_form_element' => [
				],
				'KokiAdminController_form_pickers' => [
					'vendor/bootstrap-daterangepicker/daterangepicker.css',
					'vendor/clockpicker/css/bootstrap-clockpicker.min.css',
					'vendor/jquery-asColorPicker/css/asColorPicker.min.css',
					'vendor/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css',
					'vendor/pickadate/themes/default.css',
					'vendor/pickadate/themes/default.date.css',
					'https://fonts.googleapis.com/icon?family=Material+Icons'
				],
				'KokiAdminController_form_validation_jquery' => [
				],
				'KokiAdminController_form_wizard' => [
					'vendor/jquery-smartwizard/dist/css/smart_wizard.min.css',
				],
				'KokiAdminController_map_jqvmap' => [
					'vendor/jqvmap/css/jqvmap.min.css',
				],
				'KokiAdminController_table_bootstrap_basic' => [
				],
				'KokiAdminController_table_datatable_basic' => [
					'vendor/datatables/css/jquery.dataTables.min.css',
				],
				'KokiAdminController_uc_nestable' => [
					'vendor/nestable2/css/jquery.nestable.min.css',
				],
				'KokiAdminController_uc_noui_slider' => [
					'vendor/nouislider/nouislider.min.css',
				],
				'KokiAdminController_uc_select2' => [
					'vendor/select2/css/select2.min.css',
				],
				'KokiAdminController_uc_sweetalert' => [
					'vendor/sweetalert2/dist/sweetalert2.min.css',
				],
				'KokiAdminController_uc_toastr' => [
					'vendor/toastr/css/toastr.min.css',
				],
				'KokiAdminController_uc_lightgallery' => [
					'vendor/lightgallery/css/lightgallery.min.css',
				],
				'KokiAdminController_ui_accordion' => [
				],
				'KokiAdminController_ui_alert' => [
				],
				'KokiAdminController_ui_badge' => [
				],
				'KokiAdminController_ui_button' => [
				],
				'KokiAdminController_ui_button_group' => [
				],
				'KokiAdminController_ui_card' => [
				],
				'KokiAdminController_ui_carousel' => [
				],
				'KokiAdminController_ui_dropdown' => [
				],
				'KokiAdminController_ui_grid' => [
				],
				'KokiAdminController_ui_list_group' => [
				],
				'KokiAdminController_ui_media_object' => [
				],
				'KokiAdminController_ui_modal' => [
				],
				'KokiAdminController_ui_pagination' => [
				],
				'KokiAdminController_ui_popover' => [
				],
				'KokiAdminController_ui_progressbar' => [
				],
				'KokiAdminController_ui_tab' => [
				],
				'KokiAdminController_ui_typography' => [
				],
				'KokiAdminController_widget_basic' => [
					'vendor/chartist/css/chartist.min.css',
				],
			],
			'js' => [
				'KokiAdminController_dashboard' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'js/dashboard/dashboard-1.js',
				],
				'KokiAdminController_analytics' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/peity/jquery.peity.min.js',
					'js/dashboard/analytics.js',
				],
				'KokiAdminController_customer_list' => [
					'vendor/datatables/js/jquery.dataTables.min.js',
				],
				'KokiAdminController_order' => [
					'vendor/apexchart/apexchart.js',
					'js/dashboard/order.js',
				],
				'KokiAdminController_order_list' => [
					'vendor/datatables/js/jquery.dataTables.min.js',
				],
				'KokiAdminController_review' => [
					''
				],
				'KokiAdminController_koki_element' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/chartist/js/chartist.min.js',
					'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
					'vendor/flot/jquery.flot.js',
					'vendor/flot/jquery.flot.pie.js',
					'vendor/flot/jquery.flot.resize.js',
					'vendor/flot-spline/jquery.flot.spline.min.js',
					'vendor/jquery-sparkline/jquery.sparkline.min.js',
					'js/plugins-init/sparkline-init.js',
					'vendor/peity/jquery.peity.min.js',
					'js/plugins-init/piety-init.js',
				],
				'KokiAdminController_app_calender' => [
					'vendor/chart-js/chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/jqueryui/js/jquery-ui.min.js',
					'vendor/moment/moment.min.js',
					'vendor/fullcalendar/js/main.min.js',
					'js/plugins-init/fullcalendar-init.js',
				],
				'KokiAdminController_app_profile' => [
					'vendor/lightgallery/js/lightgallery-all.min.js'
				],
				'KokiAdminController_post_details' => [
					'vendor/lightgallery/js/lightgallery-all.min.js'
				],
				'KokiAdminController_chart_chartist' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/chartist/js/chartist.min.js',
					'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
					'js/plugins-init/chartist-init.js',
				],
				'KokiAdminController_chart_chartjs' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'js/plugins-init/chartjs-init.js',
				],
				'KokiAdminController_chart_flot' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/flot/jquery.flot.js',
					'vendor/flot/jquery.flot.pie.js',
					'vendor/flot/jquery.flot.resize.js',
					'vendor/flot-spline/jquery.flot.spline.min.js',
					'js/plugins-init/flot-init.js',
				],
				'KokiAdminController_chart_morris' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/raphael/raphael.min.js',
					'vendor/morris/morris.min.js',
					'js/plugins-init/morris-init.js',
				],
				'KokiAdminController_chart_peity' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/peity/jquery.peity.min.js',
					'js/plugins-init/piety-init.js',
				],
				'KokiAdminController_chart_sparkline' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/jquery-sparkline/jquery.sparkline.min.js',
					'js/plugins-init/sparkline-init.js',
					'vendor/svganimation/vivus.min.js',
					'vendor/svganimation/svg.animation.js',
				],
				'KokiAdminController_ecom_checkout' => [
				],
				'KokiAdminController_ecom_customers' => [
				],
				'KokiAdminController_ecom_invoice' => [
				],
				'KokiAdminController_ecom_product_detail' => [
					'vendor/highlightjs/highlight.pack.min.js',
					'vendor/star-rating/jquery.star-rating-svg.js',
				],
				'KokiAdminController_ecom_product_grid' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/highlightjs/highlight.pack.min.js',
				],
				'KokiAdminController_ecom_product_list' => [
					'vendor/star-rating/jquery.star-rating-svg.js',
				],
				'KokiAdminController_ecom_product_order' => [
				],
				'KokiAdminController_email_compose' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/dropzone/dist/dropzone.js',
				],
				'KokiAdminController_email_inbox' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_email_read' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_form_ckeditor' => [
					'vendor/ckeditor/ckeditor.js',
				],
				'KokiAdminController_form_element' => [
				],
				'KokiAdminController_form_pickers' => [
					'vendor/chart-js/chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/moment/moment.min.js',
					'vendor/bootstrap-daterangepicker/daterangepicker.js',
					'vendor/clockpicker/js/bootstrap-clockpicker.min.js',
					'vendor/jquery-asColor/jquery-asColor.min.js',
					'vendor/jquery-asGradient/jquery-asGradient.min.js',
					'vendor/jquery-asColorPicker/js/jquery-asColorPicker.min.js',
					'vendor/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js',
					'vendor/pickadate/picker.js',
					'vendor/pickadate/picker.time.js',
					'vendor/pickadate/picker.date.js',
					'js/plugins-init/bs-daterange-picker-init.js',
					'js/plugins-init/clock-picker-init.js',
					'js/plugins-init/jquery-asColorPicker.init.js',
					'js/plugins-init/material-date-picker-init.js',
					'js/plugins-init/pickadate-init.js',
				],
				'KokiAdminController_form_validation_jquery' => [
					'vendor/jquery-validation/jquery.validate.min.js',
					'js/plugins-init/jquery.validate-init.js',
				],
				'KokiAdminController_form_wizard' => [
					'vendor/jquery-smartwizard/dist/js/jquery.smartWizard.js',
				],
				'KokiAdminController_map_jqvmap' => [
					'vendor/jqvmap/js/jquery.vmap.min.js',
					'vendor/jqvmap/js/jquery.vmap.world.js',
					'vendor/jqvmap/js/jquery.vmap.usa.js',
					'js/plugins-init/jqvmap-init.js',
				],
				'KokiAdminController_page_error_400' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_error_403' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_error_404' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_error_500' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_error_503' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_forgot_password' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_lock_screen' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/deznav/deznav.min.js',
				],
				'KokiAdminController_page_login' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_page_register' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
				],
				'KokiAdminController_table_bootstrap_basic' => [
				],
				'KokiAdminController_table_datatable_basic' => [
					'vendor/chart-js/chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/datatables/js/jquery.dataTables.min.js',
					'js/plugins-init/datatables.init.js',
				],
				'KokiAdminController_uc_nestable' => [
					'vendor/nestable2/js/jquery.nestable.min.js',
					'js/plugins-init/nestable-init.js',
				],
				'KokiAdminController_uc_noui_slider' => [
					'vendor/nouislider/nouislider.min.js',
					'vendor/wnumb/wNumb.js',
					'js/plugins-init/nouislider-init.js',
				],
				'KokiAdminController_uc_select2' => [
					'vendor/select2/js/select2.full.min.js',
					'js/plugins-init/select2-init.js',
				],
				'KokiAdminController_uc_sweetalert' => [
					'vendor/sweetalert2/dist/sweetalert2.min.js',
					'js/plugins-init/sweetalert.init.js',
				],
				'KokiAdminController_uc_toastr' => [
					'vendor/toastr/js/toastr.min.js',
					'js/plugins-init/toastr-init.js',
				],
				'KokiAdminController_uc_lightgallery' => [
					'vendor/lightgallery/js/lightgallery-all.min.js',
				],
				'KokiAdminController_ui_accordion' => [
				],
				'KokiAdminController_ui_alert' => [
				],
				'KokiAdminController_ui_badge' => [
				],
				'KokiAdminController_ui_button' => [
				],
				'KokiAdminController_ui_button_group' => [
				],
				'KokiAdminController_ui_card' => [
				],
				'KokiAdminController_ui_carousel' => [
				],
				'KokiAdminController_ui_dropdown' => [
				],
				'KokiAdminController_ui_grid' => [
				],
				'KokiAdminController_ui_list_group' => [
				],
				'KokiAdminController_ui_media_object' => [
				],
				'KokiAdminController_ui_modal' => [
				],
				'KokiAdminController_ui_pagination' => [
				],
				'KokiAdminController_ui_popover' => [
				],
				'KokiAdminController_ui_progressbar' => [
				],
				'KokiAdminController_ui_tab' => [
				],
				'KokiAdminController_ui_typography' => [
				],
				'KokiAdminController_widget_basic' => [
					'vendor/chart-js/Chart.bundle.min.js',
					'vendor/apexchart/apexchart.js',
					'vendor/chartist/js/chartist.min.js',
					'vendor/chartist-plugin-tooltips/js/chartist-plugin-tooltip.min.js',
					'vendor/flot/jquery.flot.js',
					'vendor/flot/jquery.flot.pie.js',
					'vendor/flot/jquery.flot.resize.js',
					'vendor/flot-spline/jquery.flot.spline.min.js',
					'vendor/jquery-sparkline/jquery.sparkline.min.js',
					'js/plugins-init/sparkline-init.js',
					'vendor/peity/jquery.peity.min.js',
					'js/plugins-init/piety-init.js',
					'js/plugins-init/widgets-script-init.js',
				]
			]
		],
	]
];
