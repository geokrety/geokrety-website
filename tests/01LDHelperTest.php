<?php

class LDHelperTest extends GKTestCase {
    const LD_JSON_SCRIPT_START = '<script type="application/ld+json">';
    const LD_JSON_SCRIPT_END = '</script>';
    const KEYWORDS_EXAMPLE = 'this,is,a,test';

    public function test_generate_ld_json_article() {
        // GIVEN
        $ldHelper = new LDHelper('geokrety.org', 'https://geokrety.org', 'https://cdn.geokrety.org/images/banners/geokrety.png');
        $testDate = date('c', 1355310732);
        $expectedDate = '2012-12-12T11:12:12+00:00';

        // WHEN
        $ldJSONArticle = $ldHelper->helpArticle(
            'this is a test',
            'UTest generation',
            'http://exemple.com/article.html',
            'http://exemple.com/image.jpg',
            self::KEYWORDS_EXAMPLE,
            'fr',
            $testDate,
            $testDate,
            'http://exemple.com/'
            );

        // THEN
        $expectedResult = self::LD_JSON_SCRIPT_START
        .'{'
        .'"@context":"http:\/\/schema.org",'
        .'"@type":"Article",'
        .'"description":"UTest generation",'
        .'"image":"http:\/\/exemple.com\/image.jpg",'
        .'"mainEntityOfPage":"http:\/\/exemple.com\/",'
        .'"url":"http:\/\/exemple.com\/article.html",'
        .'"author":{"@type":"Person","sameAs":"https:\/\/geokrety.org","name":"geokrety.org"},'
        .'"dateModified":"'.$expectedDate.'",'
        .'"datePublished":"'.$expectedDate.'",'
        .'"headline":"this is a test",'
        .'"inLanguage":"fr",'
        .'"keywords":"this,is,a,test",'
        .'"publisher":{"@type":"Organization","name":"geokrety.org","url":"https:\/\/geokrety.org",'
          .'"logo":{"@type":"ImageObject","url":"https:\/\/cdn.geokrety.org\/images\/banners\/geokrety.png"}}'
        .'}'
        .self::LD_JSON_SCRIPT_END;

        $this->assertSame($expectedResult, $ldJSONArticle);
    }

    public function test_generate_ld_json_website() {
        // GIVEN
        $ldHelper = new LDHelper('geokrety.org', 'https://geokrety.org', 'https://cdn.geokrety.org/images/banners/geokrety.png');
        $testDate = date('c', 1355310732);
        $expectedDate = '2012-12-12T11:12:12+00:00';

        // WHEN
        // ($headline, $description, $imageUrl, $name, $siteUrl, $keywords)
        $ldJSONWebSite = $ldHelper->helpWebSite(
            'this is a test',
            'UTEST SITE Description',
            'https://exemple/images/logo.jpg',
            'My WebSite',
            'https://exemple.com',
            self::KEYWORDS_EXAMPLE,
            'fr',
            $testDate,
            $testDate
            );

        // THEN
        $expectedResult = self::LD_JSON_SCRIPT_START
        .'{'
        .'"@context":"http:\/\/schema.org",'
        .'"@type":"WebSite",'
        .'"about":"UTEST SITE Description",'
        .'"headline":"this is a test",'
        .'"image":"https:\/\/exemple\/images\/logo.jpg",'
        .'"name":"My WebSite",'
        .'"url":"https:\/\/exemple.com",'
        .'"publisher":{"@type":"Organization","name":"geokrety.org","url":"https:\/\/geokrety.org",'
          .'"logo":{"@type":"ImageObject","url":"https:\/\/cdn.geokrety.org\/images\/banners\/geokrety.png"}},'
        .'"keywords":"'.self::KEYWORDS_EXAMPLE.'",'
        .'"inLanguage":"fr","dateModified":"'.$expectedDate.'","datePublished":"'.$expectedDate.'"'
        .'}'
        .self::LD_JSON_SCRIPT_END;

        $this->assertSame($expectedResult, $ldJSONWebSite);
    }

    public function test_generate_ld_json_konkret() {
        // GIVEN
        $ldHelper = new LDHelper('geokrety.org', 'https://geokrety.org', 'https://cdn.geokrety.org/images/banners/geokrety.png');
        $konkret = new \Geokrety\Domain\Konkret();
        $konkret->name = 'konkret UT';
        $konkret->description = 'konkret unit test';
        $konkret->url = 'https://example.com/konkret.php';
        $konkret->author = 'Jojo';
        $konkret->authorUrl = 'https://example.com/author.php?name=Jojo';
        $konkret->datePublished = date('c', 1355310732);
        $konkret->imageUrl = 'https://example.com/konkret.jpg';
        $konkret->keywords = self::KEYWORDS_EXAMPLE;
        $konkret->ratingCount = 10;
        $konkret->ratingAvg = 2.4;

        $log1 = new \Geokrety\Domain\KonkretLog();
        $log1->authorName = 'George';
        $log1->authorUrl = 'https://example.com/author.php?name=George';
        $log1->dateCreated = date('c', 1355310732);
        $log1->text = 'log1 content';

        $log2 = new \Geokrety\Domain\KonkretLog();
        $log2->authorName = 'Robert';
        $log2->authorUrl = 'https://example.com/author.php?name=Robert';
        $log2->dateCreated = date('c', 1355310732);
        $log2->text = 'log2 content here';

        $konkretLogs = array($log1, $log2);

        $konkret->konkretLogs = $konkretLogs;

        // WHEN
        $ldJSONWebSite = $ldHelper->helpKonkret($konkret);

        // THEN
        $expectedDate = '2012-12-12T11:12:12+00:00';
        $expectedResult = self::LD_JSON_SCRIPT_START
        .'{'
        .'"@context":"http:\/\/schema.org",'
        .'"@type":"Sculpture",'
        .'"image":"https:\/\/example.com\/konkret.jpg",'
        .'"name":"konkret UT",'
        .'"url":"https:\/\/example.com\/konkret.php",'
        .'"about":"konkret unit test",'
        .'"aggregateRating":{"@type":"AggregateRating","ratingValue":2.4,"bestRating":5,"worstRating":1,"ratingCount":10},'
        .'"author":{"@type":"Person","sameAs":"https:\/\/example.com\/author.php?name=Jojo","name":"Jojo"},'
        .'"comment":[{'
                              .'"@type":"Comment",'
                              .'"author":{'
                                    .'"@type":"Person",'
                                    .'"sameAs":"https:\/\/example.com\/author.php?name=George",'
                                    .'"name":"George"'
                                .'},'
                                .'"dateCreated":"'.$expectedDate.'",'
                                .'"text":"log1 content"'
                            .'},{'
                                .'"@type":"Comment",'
                                .'"author":{'
                                    .'"@type":"Person",'
                                    .'"sameAs":"https:\/\/example.com\/author.php?name=Robert",'
                                    .'"name":"Robert"'
                                .'},'
                                .'"dateCreated":"'.$expectedDate.'",'
                                .'"text":"log2 content here"'
                            .'}'
        .'],'
        .'"commentCount":2,'
        .'"datePublished":"'.$expectedDate.'",'
        .'"keywords":"'.self::KEYWORDS_EXAMPLE.'",'
        .'"publisher":{"@type":"Organization","name":"geokrety.org","url":"https:\/\/geokrety.org","logo":{"@type":"ImageObject","url":"https:\/\/cdn.geokrety.org\/images\/banners\/geokrety.png"}}'
        .'}'
        .self::LD_JSON_SCRIPT_END;

        // debug // var_dump($expectedResult);

        $this->assertSame($expectedResult, $ldJSONWebSite);
    }
}
