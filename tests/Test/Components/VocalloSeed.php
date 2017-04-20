<?php

namespace Test\Components;

use BlueDot\BlueDotInterface;
use BlueDot\Database\Connection;
use Test\Model\Language;
use BlueDot\Entity\PromiseInterface;
use Test\Model\Category;

require_once __DIR__.'/../../../vendor/fzaninotto/faker/src/autoload.php';

class VocalloSeed extends AbstractTestComponent
{
    public function run()
    {
        $blueDot = $this->blueDot;

        $faker = \Faker\Factory::create();

        $connection = new Connection();
        $connection
            ->setDatabaseName('langland')
            ->setHost('127.0.0.1')
            ->setPassword('root')
            ->setUser('root');

        $connection->connect();

        $blueDot
            ->setConnection($connection)
            ->useApi('vocallo_user_db');

        $languages = array(
            'croatian',
            'english',
            'french',
            'spanish',
            'german',
            'italian',
        );

        $languageModels = array();

        foreach ($languages as $language) {
            $languageModels[] = (new Language())->setLanguage($language);
        }

        $categories = array(
            'nature',
            'house',
            'road',
            'city',
            'construction',
            'programming',
            'medicine',
            'history',
            'hardware',
            'software',
        );

        foreach ($languageModels as $languageModel) {
            $languageId = $blueDot->execute('simple.insert.create_language', $languageModel)
                ->success(function(PromiseInterface $promise) {
                    return $promise->getResult()->get('last_insert_id');
                })->getResult();

            for ($a = 0; $a < 10; $a++) {
                $category = new Category();
                $category->setCategory($categories[$a]);
                $category->setLanguageId($languageId);

                $categoryId = $blueDot->execute('simple.insert.create_category', $category)
                    ->success(function(PromiseInterface $promise) {
                        return $promise->getResult()->get('last_insert_id');
                    })->getResult();

                $inserts++;

                for ($i = 0; $i < 10; $i++) {
                    $blueDot->execute('scenario.insert_word', array(
                        'insert_word' => array(
                            'language_id' => $languageId,
                            'word' => $faker->word,
                            'type' => $faker->company,
                        ),
                        'insert_word_image' => array(
                            'relative_path' => 'relative_path',
                            'absolute_path' => 'absolute_path',
                            'file_name' => 'file_name',
                            'absolute_full_path' => 'absolute_full_path',
                            'relative_full_path' => 'relative_full_path',
                        ),
                        'insert_translation' => array(
                            'translation' => $faker->words(rand(1, 25)),
                        ),
                        'insert_word_category' => array(
                            'category_id' => $categoryId,
                        ),
                    ));
                }
            }
        }
    }
}