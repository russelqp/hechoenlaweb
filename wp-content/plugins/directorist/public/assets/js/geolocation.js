
(function ($) {
    /*$("button[type='reset']").on("click", function (){
        $("#atbd_rs_value").val(0);
        $(".atbdpr_amount").text(0 + miles);
        slider_range.each(function () {
            $(this).slider({
                range: "min",
                min: 0,
                max: 1000,
                value: 0,
                slide: function (event, ui) {
                    $(".atbdpr_amount").text(ui.value + miles);
                    $("#atbd_rs_value").val(ui.value);
                }
            });
        });
        $("#at_biz_dir-location, #at_biz_dir-category").val('').trigger('change');
    });*/
    /* get current location */
    if('google' === adbdp_geolocation.select_listing_map) {
        (function () {
            var x = document.querySelector(".location-name");
            var get_lat = document.querySelector("#cityLat");
            var get_lng = document.querySelector("#cityLng");

            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition, showError);
                } else {
                    x.value = "Geolocation is not supported by this browser.";
                }
            }

            function showPosition(position) {
                lat = position.coords.latitude;
                lon = position.coords.longitude;
                displayLocation(lat, lon);
                get_lat.value = lat;
                get_lng.value = lon;
            }

            function showError(error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        x.value = "User denied the request for Geolocation.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        x.value = "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        x.value = "The request to get user location timed out.";
                        break;
                    case error.UNKNOWN_ERROR:
                        x.value = "An unknown error occurred.";
                        break;
                }
            }

            function displayLocation(latitude, longitude) {
                var geocoder;
                geocoder = new google.maps.Geocoder();
                var latlng = new google.maps.LatLng(latitude, longitude);
                geocoder.geocode(
                    {'latLng': latlng},
                    function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                var add = results[0].formatted_address;
                                var value = add.split(",");

                                count = value.length;
                                country = value[count - 1];
                                state = value[count - 2];
                                city = value[count - 3];
                                x.value = city;
                            } else {
                                x.value = "address not found";
                            }
                        } else {
                            x.value = "Geocoder failed due to: " + status;
                        }
                    }
                );
            }

            var get_loc_btn = document.querySelector(".atbd_get_loc");
            get_loc_btn.addEventListener("click", function () {
                getLocation();
            });
        })();
    } else if( 'openstreet' === adbdp_geolocation.select_listing_map ) {
        function displayLocation(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;

            $.ajax({
                url: `https://nominatim.openstreetmap.org/reverse?format=json&lon=${lng}&lat=${lat}`,
                type: 'POST',
                data: {},
                success: function (data) {
                    $('#address,.atbdp-search-address').val(data.display_name);
                    $('#cityLat').val(lat);
                    $('#cityLng').val(lng);
                }
            });
        }

        $(".atbd_get_loc").on('click', () => {
            navigator.geolocation.getCurrentPosition(displayLocation);

        })


    }
})(jQuery);
