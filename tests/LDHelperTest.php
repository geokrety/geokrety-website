<?php

use PHPUnit\Framework\TestCase;

class LDHelperTest extends TestCase {
    public function test_generate_ldjson_article() {
        // GIVEN
        $ldHelper = new LDHelper('geokrety.org', 'https://geokrety.org', 'https://cdn.geokrety.org/images/banners/geokrety.png');
        $testDate = date('c', 12345000);

        // WHEN
        $ldJSONArticle = $ldHelper->helpArticle(
            'this is a test',
            'UTest generation',
            'http://exemple.com/article.html',
            'http://exemple.com/image.jpg',
            'this,is,a,test',
            'fr',
            $testDate,
            $testDate,
            'http://exemple.com/'
            );

        // THEN
        $expectedResult = '<script type="application/ld+json">'
        .'{'
        .'"@context":"http:\/\/schema.org",'
        .'"@type":"Article",'
        .'"url":"http:\/\/exemple.com\/article.html",'
        .'"description":"UTest generation",'
        .'"image":"http:\/\/exemple.com\/image.jpg",'
        .'"publisher":{"@type":"Organization","name":"geokrety.org","url":"https:\/\/geokrety.org",'
          .'"logo":{"@type":"ImageObject","url":"https:\/\/cdn.geokrety.org\/images\/banners\/geokrety.png"}},'
        .'"keywords":"this,is,a,test",'
        .'"inLanguage":"fr",'
        .'"dateModified":"1970-05-23T21:10:00+00:00",'
        .'"datePublished":"1970-05-23T21:10:00+00:00",'
        .'"author":{"@type":"Person","name":"geokrety.org"},'
        .'"mainEntityOfPage":"http:\/\/exemple.com\/",'
        .'"headline":"this is a test"'
        .'}'
        .'</script>';

        $this->assertSame($expectedResult, $ldJSONArticle);
    }

    public function test_generate_ldjson_website() {
        // GIVEN
        $ldHelper = new LDHelper('geokrety.org', 'https://geokrety.org', 'https://cdn.geokrety.org/images/banners/geokrety.png');

        // WHEN
        // ($headline, $description, $imageUrl, $name, $siteUrl, $keywords)
        $ldJSONWebSite = $ldHelper->helpWebSite(
            'this is a test',
            'UTEST SITE Description',
            'https://exemple/images/logo.jpg',
            'My WebSite',
            'https://exemple.com',
            'this,is,a,test'
            );

        // THEN
        $expectedResult = '<script type="application/ld+json">'
        .'{'
        .'"@context":"http:\/\/schema.org",'
        .'"@type":"WebSite",'
        .'"about":"UTEST SITE Description",'
        .'"headline":"this is a test",'
        .'"image":"https:\/\/exemple\/images\/logo.jpg",'
        .'"name":"My WebSite",'
        .'"url":"https:\/\/exemple.com",'
        .'"keywords":"this,is,a,test"'
        .'}'
        .'</script>';

        $this->assertSame($expectedResult, $ldJSONWebSite);
    }
}
