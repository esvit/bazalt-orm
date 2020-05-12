<?php

namespace tests\Plugin;

class SerializableTest extends \tests\ORMBaseCase
{
    protected $model = null;

    public function setUp():void
    {
        $this->model = new Model\FilmText();
    }

    public function tearDown():void
    {
        if ($this->model->film_id) {
 //           $this->model->delete();
        }
    }

    public function testSerializable()
    {
        $this->assertEquals('test', $this->model->getSQLConnectionName());

        $this->model->film_id = 1;
        $this->model->title = 'test';
        $this->model->description = array('test' => 1);
        $this->model->save();

        $this->assertEquals(array('test' => 1), $this->model->description);

        $q = new \Bazalt\ORM\Query('SELECT description FROM film_text WHERE film_id = :id',
            array('id' => $this->model->film_id));

        $q->connection(\Bazalt\ORM\Connection\Manager::getConnection('test'));
        $obj = $q->fetch();
        $this->assertEquals('a:1:{s:4:"test";i:1;}', $obj->description);

        $model = Model\FilmText::getById($this->model->film_id);
        $this->assertEquals($model->description, $this->model->description);
    }
}
