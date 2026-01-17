xquery version "1.0";

declare variable $rate_limits_bypass external;
declare variable $short_lived_session_token external;
declare variable $gkid external;

(: use nginx as we only comunicate from inside the docker network :)
let $url := "http://nginx/api/v1/export2?gkid=" || $gkid || "&amp;DNT=1&amp;rate_limits_bypass=" || $rate_limits_bypass || "&amp;short_lived_session_token=" || $short_lived_session_token

let $new_gk := fetch:doc($url)//geokret
let $new_gk_details := fetch:doc($url|| "&amp;details=1")//geokret

let $doc_gk := fn:doc("geokrety")
let $doc_gk_details := fn:doc("geokrety-details")

return (
  if (fn:count($new_gk) > 0) then (
    delete node $doc_gk/gkxml/geokrety/geokret[@id = $gkid],
    insert node $new_gk as last into $doc_gk/gkxml/geokrety
  ) else (),

  if (fn:count($new_gk_details) > 0) then (
    delete node $doc_gk_details/gkxml/geokrety/geokret[@id = $gkid],
    insert node $new_gk_details as last into $doc_gk_details/gkxml/geokrety
  ) else ()
)

(:Take care of API rate limiting in nginx and in x per days:)
(:Check /admin/rate-limits:)
