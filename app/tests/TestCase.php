<?php
// php artisan migrate:refresh --seed --database="setup" --env="testing"

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication ()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__ . '/../../bootstrap/start.php';
    }

    public function setUp() {
        parent::setUp();
        $this->prepareForTests();
    }

    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     *
     */
    private function prepareForTests()
    {
        copy(__DIR__ . '/../database/stubdb.sqlite', __DIR__ . '/../database/testdb.sqlite');
        Mail::pretend(true);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
    }

    /**
     * Single method for customizing calls during tests.
     */
    public function __call ($method, $args)
    {
        if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
            return $this->call($method, $args[0]);
        }

        throw new BadMethodCallException;
    }

}
