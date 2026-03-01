@extends('layouts.default')

@section('content')	
	<div class="container-fluid">
		<div class="row">
			<div class="col-xl-12">
				<div class="table-responsive">
					<table id="example5" class="display mb-4 dataTablesCard">
						<thead>
							<tr>
								<th class="sorting_1 ps-0 pe-0 text-center no-data-img">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="checkAll" required="">
										<label class="custom-control-label" for="checkAll"></label>
									</div>
								</th>
								<th><strong class="font-w600 wspace-no">Customer ID</strong></th>
								<th><strong class="font-w600 wspace-no">Join Date</strong></th>
								<th><strong class="font-w600 wspace-no">Customer Name</strong></th>
								<th><strong class="font-w600 wspace-no">Location</strong></th>
								<th><strong class="font-w600 wspace-no">Total Spent</strong></th>
								<th><strong class="font-w600 wspace-no">Last Order</strong></th>
								<th><strong class="font-w600">Edit</strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox5" required="">
										<label class="custom-control-label" for="customCheckBox5"></label>
									</div>
								</td>
								<td>#5552351</td>
								<td>26 March 2020, 12:42 AM</td>
								<td>James WItcwicky</td>
								<td>Corner Street 5th London</td>
								<td>$164.52</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$14.89</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox6" required="">
										<label class="custom-control-label" for="customCheckBox6"></label>
									</div>
								</td>
								<td>#5552323</td>
								<td>26 March 2020, 12:42 AM</td>
								<td>Veronica</td>
								<td>21 King Street London</td>
								<td>$74.92</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$8.13</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox7" required="">
										<label class="custom-control-label" for="customCheckBox7"></label>
									</div>
								</td>
								<td>#5552375</td>
								<td>26 March 2020, 02:12 AM</td>
								<td>Emilia Johanson</td>
								<td>67 St. John’s RoadLondon</td>
								<td>$251.16</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$21.55</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox8" required="">
										<label class="custom-control-label" for="customCheckBox8"></label>
									</div>
								</td>
								<td>#5552311</td>
								<td>26 March 2020, 12:42 AM</td>
								<td>Olivia Shine</td>
								<td>35 Station Road London</td>
								<td>$82.46</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$42.85</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox9" required="">
										<label class="custom-control-label" for="customCheckBox9"></label>
									</div>
								</td>
								<td>#5552388</td>
								<td>26 March 2020, 02:12 AM</td>
								<td>Jessica Wong</td>
								<td>11 Church Road</td>
								<td>$24.17	</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$11.41</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox11" required="">
										<label class="custom-control-label" for="customCheckBox11"></label>
									</div>
								</td>
								<td>#5552358</td>
								<td>26 March 2020, 01:42 PM</td>
								<td>David Horison</td>
								<td>981 St. John’s Road London</td>
								<td>$24.55	</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$67.27</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox22" required="">
										<label class="custom-control-label" for="customCheckBox22"></label>
									</div>
								</td>
								<td>#5552322</td>
								<td>26 March 2020, 12:42 AM</td>
								<td>Samantha Bake</td>
								<td>79 The Drive London</td>
								<td>$22.18</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$11.41</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox33" required="">
										<label class="custom-control-label" for="customCheckBox33"></label>
									</div>
								</td>
								<td>#5552397</td>
								<td>26 March 2020, 02:12 AM</td>
								<td>Franky Sihotang</td>
								<td>6 The Avenue London`</td>
								<td>$45.86</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$91.68</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox44" required="">
										<label class="custom-control-label" for="customCheckBox44"></label>
									</div>
								</td>
								<td>#5552349</td>
								<td>26 March 2020, 12:42 AM</td>
								<td>Roberto Carlo</td>
								<td>544 Manor Road London</td>
								<td>$34.41</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$11.41</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox55" required="">
										<label class="custom-control-label" for="customCheckBox55"></label>
									</div>
								</td>
								<td>#5552356</td>
								<td>26 March 2020, 02:12 AM</td>
								<td>Rendy Greenlee</td>
								<td>32 The Green London</td>
								<td>$44.99</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$91.68</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class="sorting_1 p-0 text-center">
									<div class="form-check custom-checkbox ms-4">
										<input type="checkbox" class="form-check-input" id="customCheckBox66" required="">
										<label class="custom-control-label" for="customCheckBox66"></label>
									</div>
								</td>
								<td>#5552356</td>
								<td>26 March 2020, 02:12 AM</td>
								<td>Rendy Greenlee</td>
								<td>32 The Green London</td>
								<td>$44.99</td>
								<td><a href="#" class="btn btn-secondary btn-sm">$91.68</a></td>
								<td>
									<div class="dropdown ms-auto c-pointer">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M11.0005 12C11.0005 12.5523 11.4482 13 12.0005 13C12.5528 13 13.0005 12.5523 13.0005 12C13.0005 11.4477 12.5528 11 12.0005 11C11.4482 11 11.0005 11.4477 11.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M18.0005 12C18.0005 12.5523 18.4482 13 19.0005 13C19.5528 13 20.0005 12.5523 20.0005 12C20.0005 11.4477 19.5528 11 19.0005 11C18.4482 11 18.0005 11.4477 18.0005 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												<path d="M4.00049 12C4.00049 12.5523 4.4482 13 5.00049 13C5.55277 13 6.00049 12.5523 6.00049 12C6.00049 11.4477 5.55277 11 5.00049 11C4.4482 11 4.00049 11.4477 4.00049 12Z" stroke="#3E4954" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item text-success" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M22 11.08V12C21.9988 14.1564 21.3005 16.2547 20.0093 17.9818C18.7182 19.709 16.9033 20.9725 14.8354 21.5839C12.7674 22.1953 10.5573 22.1219 8.53447 21.3746C6.51168 20.6273 4.78465 19.2461 3.61096 17.4371C2.43727 15.628 1.87979 13.4881 2.02168 11.3363C2.16356 9.18455 2.99721 7.13631 4.39828 5.49706C5.79935 3.85781 7.69279 2.71537 9.79619 2.24013C11.8996 1.7649 14.1003 1.98232 16.07 2.85999" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M22 4L12 14.01L9 11.01" stroke="#209F84" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Accept order
											</a>
											<a class="dropdown-item text-danger" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M15 9L9 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M9 9L15 15" stroke="#F24242" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											Reject order
											</a>
											<a class="dropdown-item text-black" href="#">
											<svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 16V12" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
												<path d="M12 8H12.01" stroke="#6F6F6F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
											</svg>
											View Details
											</a>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		(function($) {
		
			var table = $('#example5').DataTable({
				searching: false,
				paging:true,
				select: false,
				//info: false,         
				lengthChange:false 
				
			});
			$('#example tbody').on('click', 'tr', function () {
				var data = table.row( this ).data();
				
			});
		
		})(jQuery);
	</script>
@endpush	