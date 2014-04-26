<?php
namespace spec\rtens\dox;

use rtens\dox\Configuration;
use watoki\scrut\Specification;

/**
 * **Start Here**
 *
 * @property Configuration configuration
 */
class IntroductionTest extends Specification {

    /**
     * Writing beautiful documentation in code
     */
    public function testCodeAndComments() {
        /**
         * do<span style="color: #bb0000; font-weight: bold;">x</span> parses the both, code and comments of the
         * methods in specification classes. Specification classes are classes that have a certain suffix
         * (usually `Test`).
         *
         * Comments, both inside the method body as well as *doc comments* of methods and classes, are
         * interpreted as [markdown] and rendered as HTML. So you can create pleasant-to-read documentation
         * (like this one) that still contains executable code which will be presented like this
         *
         * [markdown]: http://daringfireball.net/projects/markdown/
         */

        $some = 1 + 2;
        $code = 'Hello';
        $more = $code . 'World';

        /**
         * The code is executed when running the test suite so it can (and should) contain assertions
         */

        $this->assertEquals(3, $some);
        $this->assertEquals('HelloWorld', $more);

        /**
         * For more information see these specifications
         *
         * * [Parse Specification](ParseSpecification)
         * * [Parse Code And Comments](ParseCodeAndComments)
         * * [Render Specification](RenderSpecification)
         *
         * or check out the [source code] of the file containing this documentation.
         *
         * [source code]: https://github.com/rtens/dox/blob/master/spec/rtens/dox/IntroductionTest.php
         */
        null;
    }

    /**
     * Writing executable specification using an ubiquitous language
     */
    public function testDoTheGherkin() {
        /**
         * I write all of of my executable specification using methods whose names imitate the [gherkin]
         * syntax. To see examples check out the other specifications of this this project.
         * These methods start with either `given`, `when` or `then` and use underscores as placeholders
         * for arguments to create sentences. For example `given_Has_Apples('Bart', 2);`.
         *
         * In order to make these methods more readable, they are parsed specially and presented in a structured
         * way, filling the argument placeholders. The result looks like this. Hover over a step to see its code
         * or check out the [source code] of this file.
         *
         * [gherkin]: http://docs.behat.org/guides/1.gherkin.html
         * [source code]: https://github.com/rtens/dox/blob/master/spec/rtens/dox/IntroductionTest.php
         */

        $this->given_Has_Apples('Bart', 3);
        $this->given_Has_Apples('Lisa', 2);
        $this->when_Gives__Apples('Bart', 'Lisa', 2);
        $this->then_ShouldHave_Apples('Bart', 1);
        $this->then_ShouldHave_Apples('Lisa', 4);
    }

    /**
     * How to make the executable documentation of your project browsable with
     * do<span style="color: #bb0000; font-weight: bold;">x</span>
     */
    public function testPublishYourProject() {
        /**
         * #### Publish Here
         *
         * If you would like to publish your project here, [drop me a line] and I'll add it.
         *
         * [drop me a line]: http://rtens.org
         */

        /*
         * #### Publish yourself
         *
         * You can also host do<span style="color: #bb0000; font-weight: bold;">x</span> yourself.
         * To do so [install dox] on your host and the project to the configuration. The best way is to
         * overwrite the `configureProjects` method in `user/UserConfiguration.php`.
         *
         * [install dox]: http://github.com/rtens/dox
         */

        $project = $this->configuration->addProject('example-name');

        /**
         * The default folder is in `user/projects/<projectName>` but you can also overwrite it
         */

        $this->assertPathEndsWith('user/projects/example-name', $project->getFullProjectFolder());
        $project->setFullProjectFolder('/some/absolute/path');
        $this->assertPathEndsWith('/some/absolute/path', $project->getFullProjectFolder());

        /*
         * You can either download the files manually into the folder or
         * provide the URL to a [git] repository to have it downloaded automatically.
         *
         * [git]: http://git-scm.org
         */

        $project->setRepositoryUrl('http://github.com/example/project.git');

        /*
         * #### Automatic Update
         *
         * If you are using [github], you can set up a [web hook] to automatically update a project on
         * do<span style="color: #bb0000; font-weight: bold;">x</span> every time you push to the repository
         * on github. See the [specification].
         *
         * [github]: http://github.com
         * [web hook]: https://help.github.com/articles/creating-webhooks
         * [specification]: WebHook
         */
        null;
    }

    protected function setUp() {
        parent::setUp();
        $this->configuration = new Configuration(__DIR__);
    }

    private $apples = array();

    private function given_Has_Apples($name, $apples) {
        $this->apples[$name] = $apples;
    }

    private function when_Gives__Apples($giver, $receiver, $apples) {
        $this->apples[$giver] -= $apples;
        $this->apples[$receiver] += $apples;
    }

    private function then_ShouldHave_Apples($name, $apples) {
        $this->assertEquals($apples, $this->apples[$name]);
    }

    private function assertPathEndsWith($with, $path) {
        $this->assertStringEndsWith(
            str_replace('/', DIRECTORY_SEPARATOR, $with),
            str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

} 