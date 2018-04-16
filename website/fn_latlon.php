<?php

// ąśżźćłó

if (!function_exists('getLatLonBox')) {
    /*
    Funkcja getLatLonBox przelicza odleglosc w metrach na szerokosc i dlugosc geograficzna dla podanego punktu (lat/lon).
    np. kwadrat o boku 10km, na rowniku bedzie mial wymiary (lat/lon): 0.0899/0.0899 a w Polsce: 0.0899/0.1399
    W celu przyspieszenia dzialania, uzywamy tablicy z policzonymi wartosciami - dlugosc w metrach odpowiadajaca 1 deg.
    Wartosci zostaly policzone co 1 deg, od rownika po 89 stopien szerokosci geograficznej.
    */
    function getLatLonBox($lat, $lon, $meters, &$d_lat, &$d_lon)
    {
        //6371 * ACos(Cos(us.lat) * Cos(ru.lat) * Cos(ru.lon - us.lon) + Sin(us.lat) * Sin(ru.lat))

        // 1 stopien szereokosci (lat) -> metry
        // $lat1 = deg2rad($lat - 0.5);
        // $lon1 = deg2rad($lon);
        // $lat2 = deg2rad($lat + 0.5);
        // $lon2 = deg2rad($lon);
        //$OneDegLatToM = 6371000 * ACos(Cos($lat1) * Cos($lat2) * Cos($lon2 - $lon1) + Sin($lat1) * Sin($lat2));
        $OneDegLatToM = 111194.92664456;

        // "great circle distance"
        // 1 stopien dlugosci (lon) -> metry
        // $lat1 = deg2rad($lat);
        // $lon1 = deg2rad($lon - 0.5);
        // $lat2 = deg2rad($lat);
        // $lon2 = deg2rad($lon + 0.5);
        //$OneDegLonToM = 6371000 * ACos(Cos($lat1) * Cos($lat2) * Cos($lon2 - $lon1) + Sin($lat1) * Sin($lat2));

        // haversine - lepsze
        /*  R * 2 arcsin ( sqrt[ sin_square(dlat/2) + cos(lat1)*cos(lat2)*sin_square(dlon/2)] )  */
        // $lat1 = deg2rad($lat);
        // $lon1 = deg2rad($lon-0.5);
        // $lat2 = deg2rad($lat);
        // $lon2 = deg2rad($lon+0.5);
        // $halfDeltaLon = ($lon2 - $lon1) / 2;
        //$OneDegLonToM = (2 * 6371000) * asin(sqrt(cos($lat1) * cos($lat2) * sin($halfDeltaLon) * sin($halfDeltaLon)));

        //tablica od 0 do 99
        $OneDegLonToMArray = array(
                                111194.92664456,
                                111177.99068883,
                                111127.18798166,
                                111042.5340016,
                                110924.05454093,
                                110771.78569785,
                                110585.77386543,
                                110366.07571745,
                                110112.75819114,
                                109825.89846667,
                                109505.58394369,
                                109151.91221457,
                                108764.99103466,
                                108344.93828939,
                                107891.88195831,
                                107405.96007598,
                                106887.3206899,
                                106336.12181529,
                                105752.53138691,
                                105136.72720774,
                                104488.89689481,
                                103809.23782187,
                                103097.95705922,
                                102355.27131048,
                                101581.40684651,
                                100776.59943636,
                                99941.094275293,
                                99075.145910052,
                                98179.018161146,
                                97252.984042389,
                                96297.325677612,
                                95312.334214599,
                                94298.309736282,
                                93255.561169206,
                                92184.406189306,
                                91085.171125016,
                                89958.190857748,
                                88803.808719757,
                                87622.376389451,
                                86414.253784138,
                                85179.808950288,
                                83919.417951306,
                                82633.464752872,
                                81322.341105875,
                                79986.446426982,
                                78626.187676875,
                                77241.979236188,
                                75834.242779201,
                                74403.407145301,
                                72949.908208279,
                                71474.188743479,
                                69976.69829286,
                                68457.893027991,
                                66918.235611043,
                                65358.1950538,
                                63778.246574748,
                                62178.871454281,
                                60560.556888056,
                                58923.795838569,
                                57269.086884962,
                                55596.934071141,
                                53907.846752228,
                                52202.339439404,
                                50480.931643186,
                                48744.147715187,
                                46992.516688414,
                                45226.572116139,
                                43446.851909403,
                                41653.898173199,
                                39848.257041379,
                                38030.478510348,
                                36201.116271582,
                                34360.727543025,
                                32509.872899422,
                                30649.116101629,
                                28779.023924965,
                                26900.165986644,
                                25013.114572352,
                                23118.444462008,
                                21216.732754778,
                                19308.558693383,
                                17394.503487755,
                                15475.150138104,
                                13551.083257432,
                                11622.888893574,
                                9691.1543507917,
                                7756.4680109893,
                                5819.4191546091,
                                3880.5977812483,
                                1940.5944300619,
        );

        if ($lat < 0) {
            $lat = abs($lat);
        }
        $lat_rounded = round($lat, 0);
        if ($lat_rounded == 90) {
            $lat_rounded = 89;
        }
        $OneDegLonToM = $OneDegLonToMArray[$lat_rounded];

        $d_lat = (1 / $OneDegLatToM) * $meters;
        $d_lon = (1 / $OneDegLonToM) * $meters;

        // do generowania tablicy:
        //echo "$OneDegLonToM,<br/>";
        // ---------------------
    }
}

//do generowania tablicy
    /*
    for ($i=0; $i<90; $i++)
    {
        getLatLonBox($i, 0, 1000, $lat, $lon);
    }
    */
// ---------------------
