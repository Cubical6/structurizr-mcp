# Contributing

- [Introduction](#introduction)
- [Code of Conduct](#code-of-conduct)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Development Setup](#development-setup)
- [Development Workflow](#development-workflow)
- [Testing Requirements](#testing-requirements)
- [Pull Request Process](#pull-request-process)
- [Documentation Guidelines](#documentation-guidelines)
- [Support](#support)

---

## Introduction

Thank you for your interest in contributing to the Structurizr MCP Server! This guide will help you understand how to contribute effectively, whether you're reporting bugs, suggesting features, or submitting code.

> **Important:** All contributors are expected to uphold our Code of Conduct and follow the guidelines in this document.

---

## Code of Conduct

We are committed to providing a welcoming and inclusive environment for all contributors.

### Our Pledge

- Be respectful and constructive in all interactions
- Welcome diverse perspectives and backgrounds
- Listen actively to feedback and criticism
- Focus on the code, not the person
- Support and help each other succeed

### Expected Behavior

- Use welcoming and inclusive language
- Be patient and understanding with others
- Assume good intentions
- Provide constructive feedback
- Help resolve conflicts respectfully

### Unacceptable Behavior

- Harassment, discrimination, or intimidation of any kind
- Insulting, derogatory, or demeaning comments
- Personal or political attacks
- Unwelcome sexual attention or advances
- Any form of trolling or spam

### Reporting Violations

If you experience or witness a violation of this Code of Conduct:

1. **Report privately** - Email the maintainers with details
2. **Describe the incident** - Be specific about what happened
3. **Include context** - Provide relevant information (dates, locations, involved parties)
4. **Suggest resolution** - If applicable

All reports will be reviewed confidentially. Thank you for helping maintain a welcoming community.

---

## Reporting Bugs

Found a bug? Help us fix it by reporting it clearly and thoroughly.

### Before Reporting

Check if the bug has already been reported:

1. Search [existing issues](https://github.com/Cubical6/structurizr-mcp/issues)
2. Search [closed issues](https://github.com/Cubical6/structurizr-mcp/issues?q=is%3Aissue+is%3Aclosed)
3. Check [troubleshooting documentation](../troubleshooting/common-issues.md)

### Bug Report Template

When creating a new issue, please include:

```markdown
## Description
A clear description of the bug in 1-2 sentences.

## Steps to Reproduce
1. First step
2. Second step
3. Final step that shows the bug

## Expected Behavior
What should happen?

## Actual Behavior
What actually happens?

## Environment
- **PHP Version:** (e.g., 8.1.27)
- **OS:** (e.g., macOS 14.1, Ubuntu 22.04)
- **Server Version:** (e.g., 1.0.0)
- **MCP Client:** (e.g., Claude Desktop, Claude Code)

## Additional Context
Logs, error messages, code snippets, or other relevant information.

## Reproduction
If possible, provide:
- Minimal code to reproduce
- Configuration needed
- Expected vs. actual output
```

### Good Bug Reports

A good bug report includes:

- ✅ Clear, descriptive title
- ✅ Specific steps to reproduce
- ✅ Expected vs. actual behavior
- ✅ Environment details (PHP version, OS, etc.)
- ✅ Error messages or logs
- ✅ Minimal reproducible example

Example:

```
Title: "Creating workspace with very long name causes truncation"

Steps:
1. Create workspace with name longer than 100 characters
2. Check workspace name in returned JSON

Expected: Full name stored and returned
Actual: Name truncated to 100 characters
```

---

## Suggesting Features

Have an idea to improve the server? We'd love to hear it!

### Before Suggesting

Check if the feature has been discussed:

1. Search [open issues](https://github.com/Cubical6/structurizr-mcp/issues)
2. Review [feature requests](https://github.com/Cubical6/structurizr-mcp/issues?q=label%3Afeature-request)
3. Check [documentation](../README.md) for existing features

### Feature Request Template

When suggesting a feature, please include:

```markdown
## Summary
Brief description of the feature.

## Motivation
Why do we need this feature? What problem does it solve?

## Proposed Solution
How should this feature work?

## Example Usage
How would users interact with this feature?

## Benefits
What are the benefits of implementing this?

## Drawbacks
Any potential downsides or concerns?

## Alternatives
Are there alternative solutions?
```

### Good Feature Requests

A good feature request includes:

- ✅ Clear, descriptive title
- ✅ Explanation of why it's needed
- ✅ Detailed description of the feature
- ✅ Example usage or code
- ✅ Benefits and potential concerns
- ✅ Any relevant design considerations

Example:

```
Title: "Add batch workspace creation tool"

Motivation:
Users setting up multiple related workspaces need to create
them one at a time, which is time-consuming.

Proposed Solution:
Add a `create_workspaces_batch()` tool that accepts an array
of workspace configurations and creates them in parallel.

Example:
```
create_workspaces_batch([
  {"name": "Frontend", "description": "..."},
  {"name": "Backend", "description": "..."}
])
```
```

---

## Development Setup

### Prerequisites

Ensure you have the following installed:

- **PHP 8.1 or higher** - Check with `php -v`
- **Composer** - Check with `composer --version`
- **Git** - Check with `git --version`
- **Structurizr CLI** - Required for full testing

### Local Setup

#### Step 1: Clone Repository

```bash
git clone https://github.com/Cubical6/structurizr-mcp.git
cd structurizr-mcp
```

#### Step 2: Install Dependencies

```bash
# Install all dependencies including dev tools
composer install
```

#### Step 3: Download Structurizr CLI

```bash
# Create bin directory
mkdir -p bin

# Download CLI (replace URL with latest release)
cd bin
wget https://github.com/structurizr/cli/releases/download/v1.xx.x/structurizr-cli-*.zip
unzip structurizr-cli-*.zip
chmod +x structurizr.sh
cd ..
```

#### Step 4: Configure Environment

Create a `.env.local` file:

```bash
cat > .env.local << EOF
STRUCTURIZR_CLI_PATH=./bin/structurizr.sh
WORKSPACE_STORAGE_PATH=./workspaces
LOG_LEVEL=DEBUG
LOG_PATH=php://stderr
EOF
```

#### Step 5: Verify Setup

```bash
# Check PHP version
php -v

# Verify dependencies
composer check-platform-reqs

# Run quick test
php -r "require 'vendor/autoload.php'; echo 'Setup successful';"
```

### IDE Setup

#### VS Code

Create `.vscode/settings.json`:

```json
{
  "php.validate.executablePath": "/usr/bin/php",
  "php.validate.enable": true,
  "[php]": {
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "junstyle.php-cs-fixer"
  }
}
```

#### PhpStorm

1. Set PHP language level to 8.1+
2. Enable PSR-12 code style
3. Configure PHPUnit test runner
4. Set up PHPStan inspection

---

## Development Workflow

### Creating a Feature Branch

```bash
# Fetch latest changes
git fetch origin

# Create feature branch
git checkout -b feature/your-feature-name

# Make your changes...

# Commit with clear messages (see guidelines below)
git add .
git commit -m "feat: Add description of feature"

# Push to GitHub
git push origin feature/your-feature-name
```

### Branch Naming Convention

Use descriptive branch names:

- `feature/tool-name` - New tool implementation
- `fix/issue-description` - Bug fixes
- `docs/update-description` - Documentation updates
- `refactor/component-name` - Code refactoring
- `test/feature-name` - Test additions

### Commit Message Guidelines

Write clear, descriptive commit messages:

```
feat: Add new tool for batch operations
^--^  ^-----------------------------
|     |
|     +-- Summary in present tense
|
+-- Type: feat, fix, docs, style, refactor, test, chore
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation change
- `style` - Code style (formatting, missing semicolons)
- `refactor` - Code refactoring without feature changes
- `test` - Test additions or updates
- `chore` - Build process, dependencies, etc.

**Good Examples:**
- `feat: Add export_to_json tool`
- `fix: Handle missing workspace gracefully`
- `docs: Update installation guide`
- `refactor: Simplify element creation logic`

---

## Testing Requirements

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/Unit/Tools/WorkspaceToolsTest.php

# Run tests with coverage report
./vendor/bin/phpunit --coverage-html coverage

# Run only failed tests
./vendor/bin/phpunit --failed
```

### Writing Tests

#### Test File Structure

```php
<?php

namespace StructurizrMcp\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use StructurizrMcp\Tools\YourTool;

class YourToolTest extends TestCase
{
    private YourTool $tool;

    protected function setUp(): void
    {
        $this->tool = new YourTool(...);
    }

    public function test_it_does_something(): void
    {
        // Arrange
        $input = 'test';

        // Act
        $result = $this->tool->yourMethod($input);

        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Test Coverage

All contributions must maintain >95% test coverage:

```bash
# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage

# View report
open coverage/index.html
```

### Static Analysis

All code must pass PHPStan Level 8:

```bash
# Run static analysis
./vendor/bin/phpstan analyse src

# Automatically fix issues where possible
./vendor/bin/phpstan analyse src --no-progress --fix
```

### Code Style

All code must follow PSR-12 standards:

```bash
# Check code style
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style
./vendor/bin/php-cs-fixer fix
```

---

## Pull Request Process

### Before Submitting

- [ ] Create a feature branch from `main`
- [ ] Make your changes with clear commits
- [ ] Write/update tests for your changes
- [ ] Ensure all tests pass
- [ ] Update relevant documentation
- [ ] Check code style compliance
- [ ] Verify no sensitive data in commits

### Creating a Pull Request

1. **Go to GitHub** - Navigate to the repository
2. **Click "New Pull Request"** - Select your branch
3. **Fill out the template** - Provide detailed information

### PR Template

```markdown
## Description
Brief description of changes.

## Related Issues
Fixes #123

## Changes
- Change 1
- Change 2
- Change 3

## Testing
- [ ] Added tests for new functionality
- [ ] All tests passing
- [ ] Manual testing completed

## Documentation
- [ ] Updated relevant documentation
- [ ] Added examples if applicable

## Checklist
- [ ] Code follows PSR-12 style
- [ ] No breaking changes
- [ ] Tests pass
- [ ] Coverage maintained (>95%)
- [ ] PHPStan passes (Level 8)
```

### Review Process

1. **Automated Checks** - CI/CD pipeline runs tests
2. **Code Review** - Maintainers review your code
3. **Feedback** - Address any requested changes
4. **Approval** - PR is approved and ready to merge
5. **Merge** - Your changes are merged to main

### Common Review Comments

**For style issues:**
```
Consider using early return to reduce nesting:

if ($error) {
    return false;
}

// Continue...
```

**For missing tests:**
```
Please add tests for this functionality.
See tests/Unit/Tools/ExampleTest.php for examples.
```

**For documentation:**
```
Please update the relevant documentation:
docs/tools/your-tool.md
```

---

## Documentation Guidelines

### Documentation Files

**Location:** `/docs/` directory with subdirectories:
- `prologue/` - Getting started and guidelines
- `getting-started/` - Installation and setup
- `architecture/` - Deep dives and concepts
- `tools/` - Tool-specific documentation
- `resources/` - Resource documentation
- `prompts/` - Prompt documentation
- `examples/` - Real-world examples
- `troubleshooting/` - Common issues

### Style Guide

Follow these guidelines for all documentation:

#### Formatting

- Use **Markdown** for all documentation
- Use **clear headings** with proper hierarchy (h1, h2, h3)
- Use **code blocks** with language specification
- Use **blockquotes** for notes and tips
- Use **bullet points** for lists
- Use **tables** for structured data

#### Example:

```markdown
# Page Title

## Section One

Some introductory text.

> **Note:** Important information here.

### Subsection

Code example:

```php
// Your code here
```

- Bullet point 1
- Bullet point 2

| Header 1 | Header 2 |
|---|---|
| Value 1 | Value 2 |
```

#### Voice and Tone

- Use **second person** ("you") when addressing the reader
- Be **concise** and **clear**
- Use **active voice** when possible
- Avoid **jargon** without explanation
- Be **encouraging** and **helpful**

#### Structure

Every documentation page should have:

1. **Title** - Clear, descriptive heading
2. **Table of Contents** - Links to sections
3. **Introduction** - Brief overview
4. **Sections** - Main content
5. **Examples** - Real-world usage
6. **Related** - Links to related documentation

### Code Examples

Always include practical examples:

```markdown
### Creating a Workspace

Here's how to create a workspace:

**In Your Code:**

```php
$workspace = $workspaceManager->createWorkspace(
    'My App',
    'Application architecture'
);
```

**With Claude:**

```
Create a workspace named "My App"
```
```

### API Documentation

For tool and resource documentation:

```markdown
## tool_name

**Description:** What this tool does.

**Parameters:**
- `param1` (string, required) - Description
- `param2` (string, optional) - Description

**Returns:**
```json
{
  "success": true,
  "message": "Success message"
}
```

**Example:**

```
Ask Claude: "Create a workspace named 'Test'"
```
```

---

## Support

### Getting Help

- **Documentation** - Check [docs/README.md](../README.md)
- **Issues** - Search [existing issues](https://github.com/Cubical6/structurizr-mcp/issues)
- **Discussions** - Ask on GitHub discussions
- **Email** - Contact maintainers privately for sensitive issues

### Resources

- [GitHub Repository](https://github.com/Cubical6/structurizr-mcp)
- [Issue Tracker](https://github.com/Cubical6/structurizr-mcp/issues)
- [Pull Requests](https://github.com/Cubical6/structurizr-mcp/pulls)
- [Discussions](https://github.com/Cubical6/structurizr-mcp/discussions)

---

<p align="center">
  <strong>Thank you for contributing to Structurizr MCP Server!</strong>
</p>
