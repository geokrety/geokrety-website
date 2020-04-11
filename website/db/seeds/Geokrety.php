<?php

use GeoKrety\Model\Geokret;
use Phinx\Seed\AbstractSeed;

class Geokrety extends AbstractSeed {
    public function getDependencies() {
        return [
            'UserContributorsSeeder',
        ];
    }

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run() {
        $geokret = new Geokret();
        $geokret->name = '🦁 Lion Face';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🦁 Lion Face [🔗](https://emojipedia.org/lion-face/)

A friendly, cartoon-styled face of a lion—the large cat and king of the jungle—looking straight ahead. Depicted as a golden-yellow lion face with a light- or dark-brown mane, white muzzle, and neutral expression.

Often used with an affectionate tone. Does not have a full-bodied lion emoji counterpart. A lion is represented as [♌ Leo](https://emojipedia.org/leo/) in the Western zodiac.

Available as an Apple [Animoji](https://emojipedia.org/animoji/).

Apple\'s lion [previously](https://emojipedia.org/apple/ios-10.2/lion-face/) featured a timid-looking expression.

Lion Face was approved as part of [Unicode 8.0](https://emojipedia.org/unicode-8.0/) in 2015 and added to [Emoji 1.0](https://emojipedia.org/emoji-1.0/) in 2015.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/microsoft/209/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/samsung/220/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/whatsapp/224/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/twitter/228/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/facebook/200/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/emojione/211/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/openmoji/213/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/emojidex/112/lion-face_1f981.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/lg/57/lion-face_1f981.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🐤 Baby Chick';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🐤 Baby Chick [🔗](https://emojipedia.org/baby-chick/)

A yellow baby chicken (chick). Variously depicted as a chick in full profile, standing on its legs, or as a chick head. Both designs show the bird facing left, with an orange beak and feet.

Often used with an affectionate tone. May be used to represent various types of baby birds. May also be used to represent chicken foods (e.g., [poultry](https://emojipedia.org/poultry-leg/), [eggs](https://emojipedia.org/egg/)), [spring](https://emojipedia.org/spring/), [Easter](https://emojipedia.org/easter/), or slang senses of chick.

Not to be confused with [🐣 Hatching Chick](https://emojipedia.org/hatching-chick/) or [🐥Front-Facing Baby Chick](https://emojipedia.org/front-facing-baby-chick/), though their applications overlap. See also [🐔 Chicken](https://emojipedia.org/chicken/) and [🐓 Rooster](https://emojipedia.org/rooster/).

Apple, WhatsApp, and Facebook’s designs feature a chick head; other major platforms display a full-bodied chick.

Baby Chick was approved as part of [Unicode 6.0](https://emojipedia.org/unicode-6.0/) in 2010 and added to [Emoji 1.0](https://emojipedia.org/emoji-1.0/) in 2015.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/baby-chick_1f424.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/baby-chick_1f424.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/baby-chick_1f424.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/openmoji/213/baby-chick_1f424.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🦜 Parrot';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🦜 Parrot [🔗](https://emojipedia.org/parrot/)

A parrot, a brightly colored tropical bird known for its ability to mimic speech. Depicted in full profile facing left standing on its legs. Variously resembles a green lovebird (with red- and-yellow-tipped wings) or a scarlet macaw (with blue-and-yellow-striped wings).

May be used to represent various pet parrots and other pet birds.

Facebook’s parrot is orange with blue-tipped wings. Google's parrot is perched on a branch.

Parrot was approved as part of Unicode 11.0 in 2018 and added to Emoji 11.0 in 2018.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/parrot_1f99c.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/parrot_1f99c.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🦎 Lizard';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🦎 Lizard [🔗](https://emojipedia.org/lizard/)

A lizard, a scaly reptile with a long tail. Depicted as a light-green lizard on all fours from above, generally looking left with large eyes, a lightly textured or colored back, and toes outspread, as a gecko.

May be used for a variety of wild and pet lizards.

Google’s lizard was previously brown.

Lizard was approved as part of Unicode 9.0 in 2016 and added to Emoji 3.0 in 2016.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/lizard_1f98e.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/lizard_1f98e.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🦊 Fox Face';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🦊 Fox Face  [🔗](https://emojipedia.org/fox-face/)

A friendly, cartoon-styled faced of a fox, the cunning canine, looking straight ahead. Depicted as an orange fox  face with a black nose, pointed ears, and shaggy, white cheeks.

Often used with an affectionate or playful tone (e.g., slang, foxy). Does not have a full-bodied fox emoji counterpart.

Available as an Apple Animoji.

Fox Face was approved as part of Unicode 9.0 in 2016 and added to Emoji 3.0 in 2016.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/fox-face_1f98a.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/fox-face_1f98a.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/openmoji/213/fox-face_1f98a.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🦄 Unicorn Face';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🦄 Unicorn Face [🔗](https://emojipedia.org/unicorn-face/)

The face of a unicorn, a mythical creature in the form of a white horse with a single, long horn on its forehead. Generally depicted as a white horse head facing left with a pink or purple mane and a yellow or rainbow-colored horn.

In addition to the mythical unicorn, may be used to convey whimsy, fantasy, uniqueness, specialness, peace, and love. Often used for various content related to the LGBTQ community, thanks in part to its rainbow colors on many platforms. Also often used in association with “unicorn” startups. Sometimes used as a rainbow or holographic accent color.

Vendors implement the emoji with the same or similar design as 🐴 Horse Face, but with colorful hair and a horn.

Available as an Apple Animoji.

WhatsApp’s unicorn is facing right. Google’s unicorn previously had a brown mane, Samsung’s a pink mane. Twitter’s unicorn was previously purple-colored with a blue mane.

Unicorn Face was approved as part of Unicode 8.0 in 2015 and added to Emoji 1.0 in 2015.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/unicorn-face_1f984.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/unicorn-face_1f984.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/openmoji/213/unicorn-face_1f984.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🐖 Pig';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🐖 Pig [🔗](https://emojipedia.org/pig/)

A pig, a plump animal farmed for its meat, such as bacon. Depicted in light pink in full profile on all fours facing left, with a long snout and short, curly tail.

May be used to represent the animal, its food products, or various metaphorical senses of pig.

One of the 12 animals of the Chinese zodiac; 2019 is the Year of the Pig. See also 🐷 Pig Face, 🐽 Pig Nose, and 🐗 Boar.

WhatsApp’s design previously featured a wide grin.

Pig was approved as part of Unicode 6.0 in 2010 and added to Emoji 1.0 in 2015.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/pig_1f416.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/pig_1f416.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/openmoji/213/pig_1f416.png)
MISSION;
        $geokret->save();

        $geokret = new Geokret();
        $geokret->name = '🐞 Lady Beetle';
        $geokret->type = 0;
        $geokret->owner = 1;
        $geokret->mission = <<<MISSION
# 🐞 Lady Beetle [🔗](https://emojipedia.org/lady-beetle/)

A ladybug (ladybird, lady beetle), a beetle with a round, red shell with black spots. Generally depicted as a seven- or nine-spotted ladybug shown from above on its six legs, with antennae and its distinctively red-and-black, halved shell.

May be used a symbol of good luck.

Facebook’s ladybug is shown on its legs facing forward.

Lady Beetle was approved as part of Unicode 6.0 in 2010 and added to Emoji 1.0 in 2015.

![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/apple/225/lady-beetle_1f41e.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/google/223/lady-beetle_1f41e.png)
![](https://emojipedia-us.s3.dualstack.us-west-1.amazonaws.com/thumbs/240/openmoji/213/lady-beetle_1f41e.png)
MISSION;
        $geokret->save();
    }
}
