# Structurizr MCP Server

## Project Overview

Dit project implementeert een Model Context Protocol (MCP) server voor Structurizr, een tool voor het maken van software architectuurdiagrammen als code op basis van het C4 model.

## Wat is Structurizr?

Structurizr is een toolset voor het creëren van software architectuurdiagrammen via code in plaats van handmatig tekenen. Het lost het fundamentele probleem op van het gesynchroniseerd en consistent houden van architectuurdocumentatie.

### Kernwaarde
- **Single source of truth**: Eén definitie, meerdere gegenereerde views
- **Versiecontrole**: Architectuur als code in git
- **Automatische generatie**: Meerdere diagrammen uit één model
- **Consistentie**: Geen divergerende of tegenstrijdige documentatie

## Het C4 Model

Het C4 model biedt een gestructureerde methode voor het visualiseren van software architectuur met vier hiërarchische abstractieniveaus:

### 1. System Context (Niveau 1)
- Hoogste abstractieniveau
- Toont het systeem in context met gebruikers en externe systemen
- **Doel**: Big picture overzicht

### 2. Container (Niveau 2)
- Belangrijkste bouwstenen binnen een systeem
- Containers = applicaties, databases, microservices, etc.
- **Doel**: High-level technologie beslissingen en deployment architectuur

### 3. Component (Niveau 3)
- Logische groeperingen binnen containers
- Components = samenhangende functionaliteit
- **Doel**: Gedetailleerd ontwerp van een enkele container

### 4. Code (Niveau 4)
- Laagste niveau - daadwerkelijke code structuur
- **Doel**: Implementatie details (vaak gegenereerd uit code)

### Aanvullende Diagram Types
- **System Landscape**: Meerdere systemen en hun interacties
- **Dynamic**: Sequenties en runtime gedrag
- **Deployment**: Infrastructuur en deployment topologie

## Structurizr DSL

De Structurizr Domain Specific Language (DSL) is een tekstgebaseerd formaat voor het definiëren van architectuurmodellen.

### Basis Structuur

```dsl
workspace "Naam" "Beschrijving" {
    model {
        # Definieer elementen en relaties
        user = person "Gebruiker" "Beschrijving"
        system = softwareSystem "Systeem" "Beschrijving" {
            webapp = container "Web App" "Beschrijving" "Technology"
        }

        user -> system "Gebruikt"
    }

    views {
        systemContext system "SystemContext" {
            include *
            autoLayout lr
        }

        container system "Containers" {
            include *
            autoLayout tb
        }

        styles {
            element "Software System" {
                background #1168bd
                color #ffffff
            }
        }
    }
}
```

### Belangrijkste Elementen
- **person**: Gebruikers van het systeem
- **softwareSystem**: Software systemen
- **container**: Deploybare/uitvoerbare eenheden
- **component**: Logische groeperingen binnen containers
- **Relaties**: `element1 -> element2 "Beschrijving" "Technology"`

## Structurizr Interfaces

### File Formats
1. **DSL (.dsl)**: Primair formaat voor authoring
2. **JSON (.json)**: Interne workspace definitie
3. **Export formats**: PlantUML, Mermaid, DOT, etc.

### APIs
1. **Workspace API (REST)**
   - `GET /workspace/{id}` - Ophalen workspace
   - `PUT /workspace/{id}` - Update workspace
   - HMAC authenticatie

2. **Structurizr voor Java Libraries**
   - structurizr-core: Workspace programmeren
   - structurizr-client: Upload/download
   - structurizr-export: Export naar andere formaten
   - structurizr-component: Component discovery

3. **Structurizr CLI**
   - `push`: Upload DSL naar workspace
   - `pull`: Download workspace als JSON
   - `export`: Export naar verschillende formaten
   - `validate`: Valideer workspace

## Model Context Protocol (MCP)

MCP is een universele, vendor-neutrale standaard voor interacties tussen Large Language Models en externe systemen.

### Kern Concepten

1. **Servers**: Expose capabilities (tools, resources, prompts)
2. **Clients**: Verbinden met servers en integreren met LLMs
3. **Resources**: URI-geadresseerde databronnen
4. **Tools**: Uitvoerbare acties met input schemas
5. **Prompts**: Herbruikbare instructie templates

### Architectuur
- **JSON-RPC 2.0** voor message exchange
- **Transport opties**: stdio (lokaal), HTTP/SSE (web)
- **Request handlers** voor capabilities

## Structurizr MCP Server Design

### Aanbevolen Tools

#### Workspace Management
- `create_workspace(name, description)` → workspace_id
- `get_workspace(workspace_id)` → workspace JSON
- `list_workspaces()` → workspace lijst
- `delete_workspace(workspace_id)`

#### Model Building
- `add_person(workspace_id, name, description, tags?)`
- `add_software_system(workspace_id, name, description, tags?)`
- `add_container(system_id, name, description, technology?, tags?)`
- `add_component(container_id, name, description, technology?, tags?)`
- `add_relationship(source_id, dest_id, description, technology?, tags?)`

#### Views
- `create_system_context_view(system_id, key, description?)`
- `create_container_view(system_id, key, description?)`
- `create_component_view(container_id, key, description?)`
- `create_dynamic_view(element_id, key, description?)`
- `apply_auto_layout(view_key, direction)`

#### Documentation
- `add_documentation_section(workspace_id, title, content)`
- `add_adr(workspace_id, id, date, title, status, content)`

#### Export/Import
- `export_to_dsl(workspace_id)` → DSL string
- `export_to_plantuml(view_key)` → PlantUML
- `export_to_mermaid(view_key)` → Mermaid
- `import_from_dsl(dsl_content)` → workspace

#### Analysis
- `analyze_dependencies(workspace_id, element_id?)`
- `find_element(workspace_id, name)`
- `validate_workspace(workspace_id)`

### Aanbevolen Resources

```
workspace://{id}                              - Volledige workspace JSON
workspace://{id}/model                        - Alleen model
workspace://{id}/views                        - Alleen views
element://{workspace_id}/{element_id}         - Specifiek element
view://{workspace_id}/{view_key}              - Specifieke view
dsl://{workspace_id}                          - DSL representatie
```

### Aanbevolen Prompts

- `analyze_architecture(workspace_id)` - Architectuur analyse
- `review_security(workspace_id)` - Security review
- `generate_system_context(description)` - Genereer context uit beschrijving
- `suggest_improvements(workspace_id)` - Verbeter suggesties
- `explain_c4_model()` - Leg C4 model uit
- `create_example_workspace(type)` - Voorbeeld workspace

## Technologie Stack

### Aanbevolen Implementatie
- **TypeScript** met `@modelcontextprotocol/sdk`
- **Node.js** runtime
- **stdio transport** (start simpel)
- **Structurizr CLI** voor DSL parsing en export
- **REST API client** voor Structurizr Cloud/On-Premises integratie

### Project Structuur
```
structurizr-mcp/
├── src/
│   ├── index.ts              # Server entry point
│   ├── tools/                # Tool implementations
│   │   ├── workspace.ts
│   │   ├── model.ts
│   │   ├── views.ts
│   │   └── export.ts
│   ├── resources/            # Resource handlers
│   │   └── workspace.ts
│   ├── prompts/              # Prompt templates
│   │   └── analysis.ts
│   ├── structurizr/          # Structurizr client
│   │   ├── cli.ts
│   │   └── api.ts
│   └── types/                # TypeScript types
├── tests/                    # Unit & integration tests
├── docs/                     # Documentation
├── package.json
└── tsconfig.json
```

## Development Workflow

### Lokale Development
1. DSL file creëren/bewerken
2. MCP server starten
3. Tools aanroepen via MCP client
4. Workspace valideren
5. Export naar gewenst formaat

### Integratie met Structurizr Cloud
1. API credentials configureren
2. Workspace push/pull via API
3. Sync lokale en cloud workspaces

## Next Steps

Zie [TASKS.md](./TASKS.md) voor de gedetailleerde implementatie roadmap.

## Resources

- [Structurizr Documentation](https://structurizr.com)
- [C4 Model](https://c4model.com)
- [Structurizr DSL](https://github.com/structurizr/dsl)
- [Model Context Protocol](https://modelcontextprotocol.io)
- [MCP TypeScript SDK](https://github.com/modelcontextprotocol/typescript-sdk)
