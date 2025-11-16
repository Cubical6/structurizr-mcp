<?php

declare(strict_types=1);

namespace StructurizrMcp\Tests\Unit\Structurizr;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Structurizr\DslBuilder;

/**
 * Unit tests for DslBuilder
 *
 * @covers \StructurizrMcp\Structurizr\DslBuilder
 */
class DslBuilderTest extends TestCase
{
    private DslBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new DslBuilder();
    }

    public function testWorkspace(): void
    {
        $builder = $this->builder->workspace('Test Workspace', 'Test description');

        $this->assertInstanceOf(DslBuilder::class, $builder);

        $array = $builder->toArray();
        $this->assertEquals('Test Workspace', $array['name']);
        $this->assertEquals('Test description', $array['description']);
    }

    public function testWorkspaceWithEmptyDescription(): void
    {
        $this->builder->workspace('Minimal Workspace');

        $array = $this->builder->toArray();
        $this->assertEquals('Minimal Workspace', $array['name']);
        $this->assertEquals('', $array['description']);
    }

    public function testAddPerson(): void
    {
        $this->builder->workspace('Test');
        $id = $this->builder->addPerson('User', 'End user of the system');

        $this->assertStringStartsWith('person_', $id);

        $element = $this->builder->getElement($id);
        $this->assertNotNull($element);
        $this->assertEquals('person', $element['type']);
        $this->assertEquals('User', $element['name']);
        $this->assertEquals('End user of the system', $element['description']);
        $this->assertEmpty($element['tags']);
    }

    public function testAddPersonWithTags(): void
    {
        $this->builder->workspace('Test');
        $id = $this->builder->addPerson('Admin', 'Administrator', ['Admin', 'Internal']);

        $element = $this->builder->getElement($id);
        $this->assertEquals(['Admin', 'Internal'], $element['tags']);
    }

    public function testAddSoftwareSystem(): void
    {
        $this->builder->workspace('Test');
        $id = $this->builder->addSoftwareSystem('My System', 'A test system');

        $this->assertStringStartsWith('system_', $id);

        $element = $this->builder->getElement($id);
        $this->assertNotNull($element);
        $this->assertEquals('softwareSystem', $element['type']);
        $this->assertEquals('My System', $element['name']);
        $this->assertEquals('A test system', $element['description']);
        $this->assertEquals('Internal', $element['location']);
        $this->assertEmpty($element['containers']);
    }

    public function testAddSoftwareSystemWithLocation(): void
    {
        $this->builder->workspace('Test');
        $id = $this->builder->addSoftwareSystem('External API', 'Third-party API', 'External');

        $element = $this->builder->getElement($id);
        $this->assertEquals('External', $element['location']);
    }

    public function testAddSoftwareSystemWithTags(): void
    {
        $this->builder->workspace('Test');
        $id = $this->builder->addSoftwareSystem('Tagged System', 'System with tags', 'Internal', ['Critical', 'Backend']);

        $element = $this->builder->getElement($id);
        $this->assertEquals(['Critical', 'Backend'], $element['tags']);
    }

    public function testAddContainer(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('My System');
        $containerId = $this->builder->addContainer($systemId, 'Web App', 'Frontend application', 'React');

        $this->assertStringStartsWith('container_', $containerId);

        $container = $this->builder->getElement($containerId);
        $this->assertNotNull($container);
        $this->assertEquals('container', $container['type']);
        $this->assertEquals('Web App', $container['name']);
        $this->assertEquals('Frontend application', $container['description']);
        $this->assertEquals('React', $container['technology']);
        $this->assertEquals($systemId, $container['systemId']);
        $this->assertEmpty($container['components']);

        // Verify container is added to system
        $system = $this->builder->getElement($systemId);
        $this->assertContains($containerId, $system['containers']);
    }

    public function testAddContainerWithoutTechnology(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $containerId = $this->builder->addContainer($systemId, 'Database');

        $container = $this->builder->getElement($containerId);
        $this->assertEquals('', $container['technology']);
        $this->assertEquals('', $container['description']);
    }

    public function testAddContainerThrowsExceptionForInvalidSystem(): void
    {
        $this->builder->workspace('Test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('System not found: invalid_system');

        $this->builder->addContainer('invalid_system', 'Container');
    }

    public function testAddContainerThrowsExceptionForNonSystemElement(): void
    {
        $this->builder->workspace('Test');
        $personId = $this->builder->addPerson('User');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('System not found');

        $this->builder->addContainer($personId, 'Container');
    }

    public function testAddComponent(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $containerId = $this->builder->addContainer($systemId, 'API');
        $componentId = $this->builder->addComponent($containerId, 'Auth Controller', 'Handles authentication', 'Spring MVC');

        $this->assertStringStartsWith('component_', $componentId);

        $component = $this->builder->getElement($componentId);
        $this->assertNotNull($component);
        $this->assertEquals('component', $component['type']);
        $this->assertEquals('Auth Controller', $component['name']);
        $this->assertEquals('Handles authentication', $component['description']);
        $this->assertEquals('Spring MVC', $component['technology']);
        $this->assertEquals($containerId, $component['containerId']);

        // Verify component is added to container
        $container = $this->builder->getElement($containerId);
        $this->assertContains($componentId, $container['components']);
    }

    public function testAddComponentThrowsExceptionForInvalidContainer(): void
    {
        $this->builder->workspace('Test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Container not found: invalid_container');

        $this->builder->addComponent('invalid_container', 'Component');
    }

    public function testAddComponentThrowsExceptionForNonContainerElement(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Container not found');

        $this->builder->addComponent($systemId, 'Component');
    }

    public function testAddRelationship(): void
    {
        $this->builder->workspace('Test');
        $personId = $this->builder->addPerson('User');
        $systemId = $this->builder->addSoftwareSystem('System');

        $relId = $this->builder->addRelationship($personId, $systemId, 'Uses', 'HTTPS');

        $this->assertStringStartsWith('relationship_', $relId);

        $array = $this->builder->toArray();
        $this->assertArrayHasKey($relId, $array['relationships']);

        $relationship = $array['relationships'][$relId];
        $this->assertEquals($personId, $relationship['sourceId']);
        $this->assertEquals($systemId, $relationship['destinationId']);
        $this->assertEquals('Uses', $relationship['description']);
        $this->assertEquals('HTTPS', $relationship['technology']);
    }

    public function testAddRelationshipWithoutTechnology(): void
    {
        $this->builder->workspace('Test');
        $person = $this->builder->addPerson('User');
        $system = $this->builder->addSoftwareSystem('System');

        $relId = $this->builder->addRelationship($person, $system, 'Interacts with');

        $array = $this->builder->toArray();
        $relationship = $array['relationships'][$relId];
        $this->assertEquals('', $relationship['technology']);
    }

    public function testAddRelationshipWithTags(): void
    {
        $this->builder->workspace('Test');
        $person = $this->builder->addPerson('User');
        $system = $this->builder->addSoftwareSystem('System');

        $relId = $this->builder->addRelationship($person, $system, 'Uses', 'HTTPS', ['Async', 'Critical']);

        $array = $this->builder->toArray();
        $relationship = $array['relationships'][$relId];
        $this->assertEquals(['Async', 'Critical'], $relationship['tags']);
    }

    public function testAddRelationshipThrowsExceptionForInvalidSource(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source element not found: invalid_source');

        $this->builder->addRelationship('invalid_source', $systemId, 'Uses');
    }

    public function testAddRelationshipThrowsExceptionForInvalidDestination(): void
    {
        $this->builder->workspace('Test');
        $personId = $this->builder->addPerson('User');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Destination element not found: invalid_dest');

        $this->builder->addRelationship($personId, 'invalid_dest', 'Uses');
    }

    public function testAddSystemContextView(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');

        $viewKey = $this->builder->addSystemContextView($systemId, 'SystemContext', 'System context diagram');

        $this->assertEquals('SystemContext', $viewKey);

        $array = $this->builder->toArray();
        $this->assertCount(1, $array['views']);

        $view = $array['views'][0];
        $this->assertEquals('systemContext', $view['type']);
        $this->assertEquals($systemId, $view['systemId']);
        $this->assertEquals('SystemContext', $view['key']);
        $this->assertEquals('System context diagram', $view['description']);
        $this->assertEquals('lr', $view['autoLayout']);
    }

    public function testAddContainerView(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');

        $viewKey = $this->builder->addContainerView($systemId, 'Containers', 'Container diagram');

        $this->assertEquals('Containers', $viewKey);

        $array = $this->builder->toArray();
        $view = $array['views'][0];
        $this->assertEquals('container', $view['type']);
        $this->assertEquals($systemId, $view['systemId']);
    }

    public function testAddComponentView(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $containerId = $this->builder->addContainer($systemId, 'API');

        $viewKey = $this->builder->addComponentView($containerId, 'Components', 'Component diagram');

        $this->assertEquals('Components', $viewKey);

        $array = $this->builder->toArray();
        $view = $array['views'][0];
        $this->assertEquals('component', $view['type']);
        $this->assertEquals($containerId, $view['containerId']);
    }

    public function testSetViewAutoLayout(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $this->builder->addSystemContextView($systemId, 'Context');

        $this->builder->setViewAutoLayout('Context', 'tb');

        $array = $this->builder->toArray();
        $view = $array['views'][0];
        $this->assertEquals('tb', $view['autoLayout']);
    }

    public function testSetViewAutoLayoutThrowsExceptionForInvalidView(): void
    {
        $this->builder->workspace('Test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('View not found: NonExistent');

        $this->builder->setViewAutoLayout('NonExistent', 'lr');
    }

    public function testToDslWithSimpleModel(): void
    {
        $this->builder->workspace('Test Workspace', 'A test workspace');
        $personId = $this->builder->addPerson('User', 'End user');
        $systemId = $this->builder->addSoftwareSystem('System', 'The system');
        $this->builder->addRelationship($personId, $systemId, 'Uses');

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('workspace "Test Workspace" "A test workspace"', $dsl);
        $this->assertStringContainsString('model {', $dsl);
        $this->assertStringContainsString('person "User" "End user"', $dsl);
        $this->assertStringContainsString('softwareSystem "System" "The system"', $dsl);
        $this->assertStringContainsString('-> ' . $systemId . ' "Uses"', $dsl);
    }

    public function testToDslWithContainers(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $containerId = $this->builder->addContainer($systemId, 'Web App', 'Frontend', 'React');

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('softwareSystem "System"', $dsl);
        $this->assertStringContainsString('container "Web App" "Frontend" "React"', $dsl);
    }

    public function testToDslWithComponents(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $containerId = $this->builder->addContainer($systemId, 'API');
        $componentId = $this->builder->addComponent($containerId, 'Controller', 'Handles requests', 'Spring');

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('component "Controller" "Handles requests" "Spring"', $dsl);
    }

    public function testToDslWithViews(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $this->builder->addSystemContextView($systemId, 'Context');

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('views {', $dsl);
        $this->assertStringContainsString('systemContext ' . $systemId . ' "Context"', $dsl);
        $this->assertStringContainsString('include *', $dsl);
        $this->assertStringContainsString('autoLayout lr', $dsl);
        $this->assertStringContainsString('styles {', $dsl);
    }

    public function testToDslWithMultipleViews(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $containerId = $this->builder->addContainer($systemId, 'API');

        $this->builder->addSystemContextView($systemId, 'Context');
        $this->builder->addContainerView($systemId, 'Containers');
        $this->builder->addComponentView($containerId, 'Components');

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('systemContext', $dsl);
        $this->assertStringContainsString('container ' . $systemId, $dsl);
        $this->assertStringContainsString('component ' . $containerId, $dsl);
    }

    public function testToDslWithTags(): void
    {
        $this->builder->workspace('Test');
        $personId = $this->builder->addPerson('User', 'End user', ['External', 'Customer']);

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('"External,Customer"', $dsl);
    }

    public function testToDslIncludesStyles(): void
    {
        $this->builder->workspace('Test');
        $systemId = $this->builder->addSoftwareSystem('System');
        $this->builder->addSystemContextView($systemId, 'Context');

        $dsl = $this->builder->toDsl();

        $this->assertStringContainsString('styles {', $dsl);
        $this->assertStringContainsString('element "Software System"', $dsl);
        $this->assertStringContainsString('background #1168bd', $dsl);
        $this->assertStringContainsString('element "Person"', $dsl);
        $this->assertStringContainsString('shape person', $dsl);
    }

    public function testToArray(): void
    {
        $this->builder->workspace('Test', 'Description');
        $personId = $this->builder->addPerson('User');
        $systemId = $this->builder->addSoftwareSystem('System');
        $relId = $this->builder->addRelationship($personId, $systemId, 'Uses');
        $this->builder->addSystemContextView($systemId, 'Context');

        $array = $this->builder->toArray();

        $this->assertEquals('Test', $array['name']);
        $this->assertEquals('Description', $array['description']);
        $this->assertArrayHasKey($personId, $array['elements']);
        $this->assertArrayHasKey($systemId, $array['elements']);
        $this->assertArrayHasKey($relId, $array['relationships']);
        $this->assertCount(1, $array['views']);
    }

    public function testGetElement(): void
    {
        $this->builder->workspace('Test');
        $personId = $this->builder->addPerson('User');

        $element = $this->builder->getElement($personId);

        $this->assertNotNull($element);
        $this->assertEquals('User', $element['name']);
    }

    public function testGetElementReturnsNullForNonExistent(): void
    {
        $this->builder->workspace('Test');

        $element = $this->builder->getElement('nonexistent');

        $this->assertNull($element);
    }

    public function testFindElement(): void
    {
        $this->builder->workspace('Test');
        $this->builder->addPerson('Alice');
        $this->builder->addPerson('Bob');
        $this->builder->addSoftwareSystem('System A');

        $element = $this->builder->findElement('Bob');

        $this->assertNotNull($element);
        $this->assertEquals('Bob', $element['name']);
        $this->assertEquals('person', $element['type']);
    }

    public function testFindElementWithType(): void
    {
        $this->builder->workspace('Test');
        $this->builder->addPerson('Test');
        $this->builder->addSoftwareSystem('Test');

        $person = $this->builder->findElement('Test', 'person');
        $system = $this->builder->findElement('Test', 'softwareSystem');

        $this->assertNotNull($person);
        $this->assertEquals('person', $person['type']);

        $this->assertNotNull($system);
        $this->assertEquals('softwareSystem', $system['type']);
    }

    public function testFindElementReturnsNullForNonExistent(): void
    {
        $this->builder->workspace('Test');

        $element = $this->builder->findElement('NonExistent');

        $this->assertNull($element);
    }

    public function testComplexWorkflow(): void
    {
        // Build a complete C4 model
        $this->builder->workspace('E-Commerce System', 'Online shopping platform');

        // Add people
        $customer = $this->builder->addPerson('Customer', 'A customer of the system');
        $admin = $this->builder->addPerson('Admin', 'System administrator', ['Internal']);

        // Add systems
        $ecommerce = $this->builder->addSoftwareSystem('E-Commerce System', 'Main system', 'Internal');
        $payment = $this->builder->addSoftwareSystem('Payment Gateway', 'Handles payments', 'External');

        // Add containers
        $webapp = $this->builder->addContainer($ecommerce, 'Web App', 'Frontend', 'React');
        $api = $this->builder->addContainer($ecommerce, 'API', 'Backend API', 'Node.js');
        $db = $this->builder->addContainer($ecommerce, 'Database', 'Stores data', 'PostgreSQL');

        // Add components
        $authCtrl = $this->builder->addComponent($api, 'Auth Controller', 'Authentication', 'Express');
        $orderCtrl = $this->builder->addComponent($api, 'Order Controller', 'Order management', 'Express');

        // Add relationships
        $this->builder->addRelationship($customer, $webapp, 'Uses', 'HTTPS');
        $this->builder->addRelationship($webapp, $api, 'Calls', 'REST/JSON');
        $this->builder->addRelationship($api, $db, 'Reads/writes', 'SQL');
        $this->builder->addRelationship($api, $payment, 'Processes payments', 'HTTPS');

        // Add views
        $this->builder->addSystemContextView($ecommerce, 'SystemContext', 'System overview');
        $this->builder->addContainerView($ecommerce, 'Containers', 'Container view');
        $this->builder->addComponentView($api, 'APIComponents', 'API components');
        $this->builder->setViewAutoLayout('Containers', 'tb');

        // Verify the model
        $array = $this->builder->toArray();
        $this->assertCount(9, $array['elements']); // 2 people + 2 systems + 3 containers + 2 components
        $this->assertCount(4, $array['relationships']);
        $this->assertCount(3, $array['views']);

        // Verify DSL generation
        $dsl = $this->builder->toDsl();
        $this->assertStringContainsString('E-Commerce System', $dsl);
        $this->assertStringContainsString('Customer', $dsl);
        $this->assertStringContainsString('Payment Gateway', $dsl);
        $this->assertStringContainsString('Web App', $dsl);
        $this->assertStringContainsString('systemContext', $dsl);
        $this->assertStringContainsString('container ' . $ecommerce, $dsl);
        $this->assertStringContainsString('component ' . $api, $dsl);
    }
}
