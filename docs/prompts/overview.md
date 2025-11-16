# MCP Prompts Overview

## Introduction

MCP prompts are pre-configured conversation templates that help you interact with the Structurizr MCP server more effectively. They provide structured guidance for common tasks like analyzing architectures, generating models, and understanding the C4 methodology.

## What Are MCP Prompts?

In the Model Context Protocol, prompts are reusable conversation starters that:

- Embed contextual information into the conversation
- Guide the LLM with specific instructions and frameworks
- Load relevant workspace data automatically
- Provide consistent, structured outputs

Think of prompts as conversation templates that pre-populate the context with the right information and questions.

## How Prompts Work

When you invoke an MCP prompt, it:

1. **Accepts Parameters**: You provide input like workspace ID or focus area
2. **Loads Data**: The prompt fetches relevant workspace information
3. **Builds Context**: It creates a structured conversation with:
   - User messages with your data
   - Resource embeddings (workspace JSON)
   - Specific analysis frameworks or guidelines
4. **Returns Messages**: The LLM receives a complete conversation context to respond to

### Prompt Structure

Each prompt returns an array of messages that form a conversation:

```php
[
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'Analysis request...']
            ]
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'resource',
                    'resource' => [
                        'uri' => 'structurizr://workspace/123',
                        'mimeType' => 'application/json',
                        'text' => '{workspace data}'
                    ]
                ]
            ]
        ],
        [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'Specific analysis framework...']
            ]
        ]
    ]
]
```

## Types of Prompts

The Structurizr MCP server provides two categories of prompts:

### Analysis Prompts

Help you understand and improve existing architecture models:

- **analyze_architecture**: Comprehensive analysis across 7 dimensions
- **review_security**: Security-focused review with 6-point checklist
- **suggest_improvements**: Actionable recommendations for enhancement

### Generation Prompts

Help you create new architecture models and learn the C4 methodology:

- **generate_system_context**: Create system context from description
- **create_from_description**: Build complete multi-level C4 model
- **explain_c4_model**: Learn about C4 methodology
- **create_example_workspace**: Generate realistic example architectures

## Benefits of Using Prompts

### Consistency

Prompts ensure that analyses follow the same framework every time, making results comparable across different workspaces.

### Expertise

Each prompt embeds best practices and architectural knowledge, giving you expert-level guidance.

### Efficiency

Instead of explaining what you want each time, prompts provide pre-built templates for common tasks.

### Learning

Generation prompts like `explain_c4_model` and `create_example_workspace` help you learn C4 methodology through examples.

## When to Use Prompts vs Tools

### Use Prompts When:

- You want analysis, insights, or recommendations
- You need guidance on creating architecture models
- You want to learn about C4 methodology
- You need structured review frameworks

### Use Tools When:

- You want to perform specific actions (create, update, delete)
- You need to manipulate workspace data directly
- You want to export or validate workspaces
- You need programmatic control

## Combining Prompts and Tools

The most powerful workflows combine both:

1. **Start with a Generation Prompt**: Use `generate_system_context` to get guidance
2. **Execute with Tools**: Use the recommended tool calls to build the model
3. **Analyze with Analysis Prompt**: Use `analyze_architecture` to review
4. **Refine with Tools**: Make improvements based on suggestions
5. **Review Security**: Use `review_security` for security assessment

### Example Workflow

```
1. Use 'generate_system_context' prompt
   → Get structured guidance on what to create

2. Use tools: create_workspace, add_person, add_software_system, etc.
   → Build the actual model

3. Use 'analyze_architecture' prompt
   → Get comprehensive analysis

4. Use tools: add_container, add_relationship, etc.
   → Add missing elements based on analysis

5. Use 'review_security' prompt
   → Identify security gaps

6. Use tools: add_documentation_section, add_adr
   → Document security decisions

7. Use 'export_to_dsl' tool
   → Save to version control
```

## Prompt Categories in Detail

### Analysis Prompts

Analysis prompts load your workspace data and provide structured review frameworks. They're read-only and don't modify your workspace.

**Best for:**
- Understanding existing architectures
- Identifying issues and risks
- Getting improvement recommendations
- Security reviews
- Documentation quality assessment

### Generation Prompts

Generation prompts provide guidance and templates for creating new models. They help you understand what to build and how to build it.

**Best for:**
- Starting new workspaces
- Learning C4 methodology
- Getting examples of best practices
- Understanding architecture patterns

## Advanced Usage

### Custom Focus Areas

Some prompts accept parameters to customize their behavior:

```javascript
// Focus on specific improvement area
suggest_improvements(workspaceId, focusArea: 'scalability')

// Generate specific example type
create_example_workspace(type: 'microservices')
```

### Resource Embedding

Prompts automatically embed workspace data as MCP resources, making the full context available to the LLM without token overhead in the prompt itself.

### Multi-Step Analysis

You can chain multiple prompts for comprehensive reviews:

1. `analyze_architecture` - Overall assessment
2. `review_security` - Security deep-dive
3. `suggest_improvements` with specific focus areas - Targeted recommendations

## Best Practices

### Start Broad, Then Focus

Begin with general analysis, then use focused prompts:

```
1. analyze_architecture - Get overview
2. review_security - Security specifics
3. suggest_improvements(focusArea: 'performance') - Targeted improvements
```

### Use Examples to Learn

Before creating your own architecture, study examples:

```
1. explain_c4_model - Understand methodology
2. create_example_workspace(type: 'ecommerce') - See it in action
3. generate_system_context - Get guidance for your system
```

### Document Based on Prompts

Use prompt output to create documentation:

```
1. Run analyze_architecture
2. Use output to create documentation_section
3. Run review_security
4. Use output to create ADR for security decisions
```

### Iterate

Don't expect perfect results on first try:

```
1. Generate initial model
2. Analyze it
3. Refine based on feedback
4. Re-analyze
5. Repeat until satisfied
```

## Common Patterns

### Pattern 1: New System Documentation

```
1. explain_c4_model (learn)
2. generate_system_context (get guidance)
3. Use tools to build model
4. analyze_architecture (review)
5. Refine based on feedback
```

### Pattern 2: Security Review

```
1. review_security (identify issues)
2. Use tools to add missing elements
3. add_adr to document security decisions
4. review_security again (verify improvements)
```

### Pattern 3: Learning from Examples

```
1. create_example_workspace (see best practices)
2. export_to_dsl (study the structure)
3. create_from_description (build your own)
4. Compare with example
```

### Pattern 4: Continuous Improvement

```
1. analyze_architecture (baseline)
2. suggest_improvements (get recommendations)
3. Use tools to implement changes
4. analyze_architecture (verify improvements)
5. Measure progress over time
```

## Next Steps

- **Reference Guide**: See [prompts/reference.md](reference.md) for detailed documentation of each prompt
- **Examples**: Check [examples/](../examples/) for practical prompt usage scenarios
- **Tools**: Learn about [tools/](../tools/) to understand how to implement prompt suggestions
