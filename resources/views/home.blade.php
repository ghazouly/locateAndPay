@extends('layouts.app')
@section('style')
    <style>
    #map {
        width: 100%;
        height: 400px;
    }
    .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    #searchInput {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 50%;
    }
    #searchInput:focus {
        border-color: #4d90fe;
    }

    .alert.parsley {
    margin-top: 5px;
    margin-bottom: 0px;
    padding: 10px 15px 10px 15px;
    }
    .check .alert {
        margin-top: 20px;
    }
    .credit-card-box .panel-title {
        display: inline;
        font-weight: bold;
    }
    .credit-card-box .display-td {
        display: table-cell;
        vertical-align: middle;
        width: 100%;
    }
    .credit-card-box .display-tr {
        display: table-row;
    }

    </style>
@stop

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Google Map</div>


                <div class="panel-body">
                  <input id="searchInput" class="controls" type="text" placeholder="Enter a location">
                  <div id="map"></div>
                  <ul id="geoData">
                      <li>Full Address: <span id="location"></span></li>
                      <li>Postal Code: <span id="postal_code"></span></li>
                      <li>Country: <span id="country"></span></li>
                      <li>Latitude: <span id="lat"></span></li>
                      <li>Longitude: <span id="lon"></span></li>
                  </ul>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Stripe Payment</div>


                <div class="panel-body">
                  <div class="col-md-12">
                    {!! Form::open(['url' => route('order-post'), 'data-parsley-validate', 'id' => 'payment-form']) !!}
                      @if ($message = Session::get('success'))
                      <div class="alert alert-success alert-block">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                              <strong>{{ $message }}</strong>
                      </div>
                      @endif
                      <div class="form-group" id="product-group">
                          {!! Form::label('plane', 'Select Plan:') !!}
                          {!! Form::select('plane', ['google' => 'Google ($10)', 'game' => 'Game ($20)', 'movie' => 'Movie ($15)'], 'Book', [
                              'class'                       => 'form-control',
                              'required'                    => 'required',
                              'data-parsley-class-handler'  => '#product-group'
                              ]) !!}
                      </div>
                      <div class="form-group" id="cc-group">
                          {!! Form::label(null, 'Credit card number:') !!}
                          {!! Form::text(null, null, [
                              'class'                         => 'form-control',
                              'required'                      => 'required',
                              'data-stripe'                   => 'number',
                              'data-parsley-type'             => 'number',
                              'maxlength'                     => '16',
                              'data-parsley-trigger'          => 'change focusout',
                              'data-parsley-class-handler'    => '#cc-group'
                              ]) !!}
                      </div>
                      <div class="form-group" id="ccv-group">
                          {!! Form::label(null, 'CVC (3 or 4 digit number):') !!}
                          {!! Form::text(null, null, [
                              'class'                         => 'form-control',
                              'required'                      => 'required',
                              'data-stripe'                   => 'cvc',
                              'data-parsley-type'             => 'number',
                              'data-parsley-trigger'          => 'change focusout',
                              'maxlength'                     => '4',
                              'data-parsley-class-handler'    => '#ccv-group'
                              ]) !!}
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group" id="exp-m-group">
                              {!! Form::label(null, 'Ex. Month') !!}
                              {!! Form::selectMonth(null, null, [
                                  'class'                 => 'form-control',
                                  'required'              => 'required',
                                  'data-stripe'           => 'exp-month'
                              ], '%m') !!}
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group" id="exp-y-group">
                              {!! Form::label(null, 'Ex. Year') !!}
                              {!! Form::selectYear(null, date('Y'), date('Y') + 10, null, [
                                  'class'             => 'form-control',
                                  'required'          => 'required',
                                  'data-stripe'       => 'exp-year'
                                  ]) !!}
                          </div>
                        </div>
                      </div>
                        <div class="form-group">
                            {!! Form::submit('Place order!', ['class' => 'btn btn-lg btn-block btn-primary btn-order', 'id' => 'submitBtn', 'style' => 'margin-bottom: 10px;']) !!}
                        </div>
                        <div class="row">
                          <div class="col-md-12">
                              <span class="payment-errors" style="color: red;margin-top:10px;"></span>
                          </div>
                        </div>
                    {!! Form::close() !!}
                  </div>

                </div>

            </div>
        </div>
    </div>





</div>
@endsection

@section('script')
    <script type='text/javascript'>
    function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: 30.0440685, lng: 31.23551199999997},
          zoom: 13
        });
        var input = document.getElementById('searchInput');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        var infowindow = new google.maps.InfoWindow();
        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });

        autocomplete.addListener('place_changed', function() {
            infowindow.close();
            marker.setVisible(false);
            var place = autocomplete.getPlace();
            if (!place.geometry) {
                window.alert("Autocomplete's returned place contains no geometry");
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }
            marker.setIcon(({
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(35, 35)
            }));
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            var address = '';
            if (place.address_components) {
                address = [
                  (place.address_components[0] && place.address_components[0].short_name || ''),
                  (place.address_components[1] && place.address_components[1].short_name || ''),
                  (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }

            infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
            infowindow.open(map, marker);

            //Location details
            for (var i = 0; i < place.address_components.length; i++) {
                if(place.address_components[i].types[0] == 'postal_code'){
                    document.getElementById('postal_code').innerHTML = place.address_components[i].long_name;
                }
                if(place.address_components[i].types[0] == 'country'){
                    document.getElementById('country').innerHTML = place.address_components[i].long_name;
                }
            }
            document.getElementById('location').innerHTML = place.formatted_address;
            document.getElementById('lat').innerHTML = place.geometry.location.lat();
            document.getElementById('lon').innerHTML = place.geometry.location.lng();
        });


        <!-- Stripe -->

        window.ParsleyConfig = {
            errorsWrapper: '<div></div>',
            errorTemplate: '<div class="alert alert-danger parsley" role="alert"></div>',
            errorClass: 'has-error',
            successClass: 'has-success'
        };

        Stripe.setPublishableKey("<?php echo env('STRIPE_PUBLISHABLE_SECRET') ?>");
        jQuery(function($) {
            $('#payment-form').submit(function(event) {
                var $form = $(this);
                $form.parsley().subscribe('parsley:form:validate', function(formInstance) {
                    formInstance.submitEvent.preventDefault();
                    alert();
                    return false;
                });
                $form.find('#submitBtn').prop('disabled', true);
                Stripe.card.createToken($form, stripeResponseHandler);
                return false;
            });
        });
        function stripeResponseHandler(status, response) {
            var $form = $('#payment-form');
            if (response.error) {
                $form.find('.payment-errors').text(response.error.message);
                $form.find('.payment-errors').addClass('alert alert-danger');
                $form.find('#submitBtn').prop('disabled', false);
                $('#submitBtn').button('reset');
            } else {
                var token = response.id;
                $form.append($('<input type="hidden" name="stripeToken" />').val(token));
                $form.get(0).submit();
            }
        };


    }
    </script>
    <script src='https://maps.googleapis.com/maps/api/js?key=AIzaSyAk0zR3sx7x3EG1MCl1HtBg8O3LJunUTHk&libraries=places&callback=initMap' async defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="http://parsleyjs.org/dist/parsley.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

@stop
