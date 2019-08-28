<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Common\Persistence\ObjectManager;

class TagFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        /*$this->createMany(10, 'tag', function($i) {
            $tag = new Tag();
            $tag->setName($this->faker->realText(20))
                ->setSlug($this->faker->slug);
            return $tag;
        });
        $manager->flush();*/
    }
}
