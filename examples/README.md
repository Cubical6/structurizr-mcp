# Structurizr DSL Examples

This directory contains example Structurizr DSL files demonstrating different architecture patterns.

## Examples

### ecommerce-example.dsl

A simple e-commerce platform showing:
- Customer and Administrator personas
- E-commerce system with multiple containers (Web App, API Gateway, Services, Database)
- External systems (Payment Gateway, Email Service)
- System context and container views

This example demonstrates:
- C4 model hierarchy (Person → System → Container)
- Relationships between elements
- Internal vs External systems
- Technology annotations
- Multiple view types

## Using These Examples

You can create similar architectures using the Structurizr MCP server by asking Claude to:

```
Create a workspace based on the e-commerce example:
1. Add a customer and administrator
2. Create an e-commerce system with web app, API gateway, and services
3. Add external payment and email systems
4. Define the relationships
5. Generate both system context and container views
```

## More Examples Coming Soon

- Microservices architecture
- Monolithic application
- Serverless architecture
- Multi-tenant SaaS platform
