<?php

/**
 * JSON-LD Helper, a way to generate JSON-LD ( W3C Recommendation as of 16 January 2014 : cf. https://json-ld.org/ )
 * This helps modern search engines to get website meta-information in a standardized way.
 **/
class LDHelper {
    public $lgGenerator;

    public $geokretyName;
    public $geokretyUrl;
    public $geokretyLogoUrl;

    public $geokretyLogo;
    public $geokretyPerson;
    public $geokretyOrganization;

    public function __construct($geokretyName, $geokretyUrl, $geokretyLogoUrl) {
        $this->lgGenerator = new LDGeneratorFactory();
        $this->geokretyName = $geokretyName;
        $this->geokretyUrl = $geokretyUrl;
        $this->geokretyLogoUrl = $geokretyLogoUrl;

        $this->geokretyLogo = $this->lgGenerator->createLdContext('ImageObject', [
            'url' => $geokretyLogoUrl,
        ]);
        $this->geokretyPerson = $this->createAuthor($geokretyName, $geokretyUrl);
        $this->geokretyOrganization = $this->createOrganization($geokretyName, $geokretyUrl);
    }

    public function createOrganization($orgName, $orgUrl) {
        return $this->lgGenerator->createLdContext('Organization', [
                'name' => $orgName,
                'url' => $orgUrl,
                'logo' => $this->geokretyLogo,
            ]);
    }

    public function createAuthor($authorName, $authorUrl) {
        return $this->lgGenerator->createLdContext('Person', [
                'name' => $authorName,
                'sameAs' => $authorUrl,
            ]);
    }

    /**
     * schema used: http://schema.org/Article.
     *
     * help lang (inLanguage):
     * - https://tools.ietf.org/html/bcp47#section-2.1
     * - https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
     */
    public function helpArticle($headline, $description, $articleUrl, $imageUrl, $keywords, $lang,
                                $dateModified, $datePublished, $mainEntityOfPage) {
        $context = $this->lgGenerator->createLdContext('Article', [
            'headline' => $headline,
            'description' => $description,
            'url' => $articleUrl,
            'image' => $imageUrl,
            'author' => $this->geokretyPerson,
            'publisher' => $this->geokretyOrganization,
            'keywords' => $keywords,
            'inLanguage' => $lang,
            'dateModified' => $dateModified,
            'datePublished' => $datePublished,
            'mainEntityOfPage' => $mainEntityOfPage,
        ]);

        return $context->generate();
    }

    /**
     * schema used: http://schema.org/WebSite.
     */
    public function helpWebSite($headline, $description, $imageUrl, $name, $siteUrl, $keywords, $lang,
                                $dateModified, $datePublished) {
        $context = $this->lgGenerator->createLdContext('WebSite', [
            'about' => $description,
            'headline' => $headline,
            'url' => $siteUrl,
            'image' => $imageUrl,
            'name' => $name,
            'keywords' => $keywords,
            'publisher' => $this->geokretyOrganization,
            'inLanguage' => $lang,
            'dateModified' => $dateModified,
            'datePublished' => $datePublished,
        ]);

        return $context->generate();
    }

    public function createAggregateRating($ratingValue, $ratingCount) {
        return $this->lgGenerator->createLdContext('AggregateRating', [
            'reviewCount' => null,
            'ratingValue' => $ratingValue,
            'bestRating' => 5,
            'worstRating' => 1,
            'ratingCount' => $ratingCount,
            ]);
    }

    /**
     * schema used: http://schema.org/Sculpture.
     */
    public function helpKonkret($konkret) {
        // sculpture attributes
        $sculptureContent = [
            'name' => $konkret->name,
            'url' => $konkret->url,
            'author' => $this->createAuthor($konkret->author, $konkret->authorUrl),
            'datePublished' => $konkret->datePublished,
            'image' => $konkret->imageUrl,
            'keywords' => $konkret->keywords,
            'about' => $konkret->description,
            'publisher' => $this->geokretyOrganization,
        ];
        // if rating is present
        if (isset($konkret->ratingCount) && $konkret->ratingCount > 0) {
            $sculptureContent['aggregateRating'] = $this->createAggregateRating($konkret->ratingAvg, $konkret->ratingCount);
        }

        // comments
        if (isset($konkret->konkretLogs) && is_array($konkret->konkretLogs)) {
            $allComments = [];
            $sculptureContent['commentCount'] = count($konkret->konkretLogs);
            foreach ($konkret->konkretLogs as &$konkretLog) {
                $commentContent = [
                    'author' => $this->createAuthor($konkretLog->authorName, $konkretLog->authorUrl),
                    'text' => $konkretLog->text,
                    'dateCreated' => $konkretLog->dateCreated,
                ];
                $comment = $this->lgGenerator->createLdContext('Comment', $commentContent);
                array_push($allComments, $comment->getProperties());
            }
            $sculptureContent['comment'] = $allComments;
        }

        $sculpture = $this->lgGenerator->createLdContext('Sculpture', $sculptureContent);

        return $sculpture->generate();
    }
}
