<?php

namespace Im0rtality\TaskSchedulerBundle\Twig;

class MongoDateExtension extends \Twig_Extension
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'mongoDate_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('mongoDate', array($this, 'convertMongoDateFilter')),
        );
    }

    public function convertMongoDateFilter(\MongoDate $mongoDate)
    {
        return new \DateTime('@' . $mongoDate->sec);
    }
}
