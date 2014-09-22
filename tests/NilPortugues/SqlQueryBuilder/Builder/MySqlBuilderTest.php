<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/4/14
 * Time: 12:40 AM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\NilPortugues\SqlQueryBuilder\Builder;

use NilPortugues\SqlQueryBuilder\Builder\MySqlBuilder;
use NilPortugues\SqlQueryBuilder\Manipulation\Select;

/**
 * Class MySqlBuilderTest
 * @package Tests\NilPortugues\SqlQueryBuilder\BuilderInterface
 */
class MySqlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MySqlBuilder
     */
    protected $writer;

    /**
     *
     */
    protected function setUp()
    {
        $this->writer = new MySqlBuilder();

    }

    /**
     *
     */
    protected function tearDown()
    {
        $this->writer = null;

    }

    /**
     * @test
     */
    public function it_should_wrap_table_names()
    {
        $query = new Select('user');

        $expected = 'SELECT `user`.* FROM `user`';
        $this->assertSame($expected, $this->writer->write($query));
    }

    /**
     * @test
     */
    public function it_should_wrap_column_names()
    {
        $query = new Select('user', array('user_id', 'name'));

        $expected = 'SELECT `user`.`user_id`, `user`.`name` FROM `user`';
        $this->assertSame($expected, $this->writer->write($query));
    }

    /**
     * @test
     */
    public function it_should_wrap_column_alias()
    {
        $query = new Select('user', array('userId' => 'user_id', 'name' => 'name'));

        $expected = 'SELECT `user`.`user_id` AS `userId`, `user`.`name` AS `name` FROM `user`';
        $this->assertSame($expected, $this->writer->write($query));
    }
}
