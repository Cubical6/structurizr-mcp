<?php

declare(strict_types=1);

namespace StructurizrMcp\Prompts;

use Mcp\Capability\Attribute\McpPrompt;
use Mcp\Capability\Attribute\Schema;
use Psr\Log\LoggerInterface;
use StructurizrMcp\Exception\WorkspaceNotFoundException;
use StructurizrMcp\Structurizr\WorkspaceManager;

/**
 * Analysis prompts for architectural insights and reviews
 *
 * These prompts provide conversation context for analyzing C4 architecture models,
 * reviewing security aspects, and suggesting improvements.
 */
class AnalysisPrompts
{
    public function __construct(
        private readonly WorkspaceManager $workspaceManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Analyze workspace architecture and provide insights
     *
     * This prompt embeds the full workspace data and asks the LLM to analyze:
     * - Architecture patterns used
     * - Complexity assessment
     * - Dependencies between elements
     * - Potential risks
     * - Suggested improvements
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string, resource?: array{uri: string, mimeType: string, text: string}}>}>}
     */
    #[McpPrompt(
        name: 'analyze_architecture',
        description: 'Analyze a C4 workspace architecture and provide comprehensive insights on patterns, complexity, dependencies, and risks'
    )]
    public function analyzeArchitecture(
        #[Schema(description: 'Workspace ID to analyze', minLength: 1, maxLength: 50)]
        string $workspaceId
    ): array {
        $this->logger->info('Generating architecture analysis prompt', ['workspaceId' => $workspaceId]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            $workspaceData = $workspace->toArray();

            // Count elements for context
            $elementCount = count($workspaceData['model']['people'] ?? [])
                + count($workspaceData['model']['softwareSystems'] ?? []);

            $viewCount = count($workspaceData['views']['systemContextViews'] ?? [])
                + count($workspaceData['views']['containerViews'] ?? [])
                + count($workspaceData['views']['componentViews'] ?? [])
                + count($workspaceData['views']['dynamicViews'] ?? []);

            $jsonData = json_encode($workspaceData, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                throw new \RuntimeException("Failed to encode workspace data");
            }

            return [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Please analyze the following C4 architecture model:\n\n" .
                                    "**Workspace**: {$workspace->name}\n" .
                                    "**Description**: {$workspace->description}\n" .
                                    "**Elements**: {$elementCount}\n" .
                                    "**Views**: {$viewCount}\n\n" .
                                    "I need a comprehensive analysis covering:"
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'resource',
                                'resource' => [
                                    'uri' => "structurizr://workspace/{$workspaceId}",
                                    'mimeType' => 'application/json',
                                    'text' => $jsonData
                                ]
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "**Analysis Requirements:**\n\n" .
                                    "1. **Architecture Patterns**: Identify architectural patterns (e.g., microservices, layered, event-driven)\n" .
                                    "2. **Complexity Assessment**: Evaluate model complexity and maintainability\n" .
                                    "3. **Dependencies**: Analyze relationships and coupling between elements\n" .
                                    "4. **Completeness**: Check if all C4 levels are appropriately modeled\n" .
                                    "5. **Best Practices**: Compare against C4 model best practices\n" .
                                    "6. **Potential Risks**: Identify architectural risks or anti-patterns\n" .
                                    "7. **Improvement Suggestions**: Recommend specific enhancements\n\n" .
                                    "Please provide detailed insights for each area."
                            ]
                        ]
                    ]
                ]
            ];
        } catch (WorkspaceNotFoundException $e) {
            $this->logger->error('Workspace not found for analysis', [
                'workspaceId' => $workspaceId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Review security aspects of the architecture
     *
     * This prompt focuses on security considerations:
     * - Authentication and authorization flows
     * - Data protection mechanisms
     * - External system interactions
     * - Security boundaries
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string, resource?: array{uri: string, mimeType: string, text: string}}>}>}
     */
    #[McpPrompt(
        name: 'review_security',
        description: 'Review security aspects of a C4 architecture model including authentication, authorization, data protection, and security boundaries'
    )]
    public function reviewSecurity(
        #[Schema(description: 'Workspace ID to review for security', minLength: 1, maxLength: 50)]
        string $workspaceId
    ): array {
        $this->logger->info('Generating security review prompt', ['workspaceId' => $workspaceId]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            $workspaceData = $workspace->toArray();

            // Count external systems for security context
            $externalSystems = array_filter(
                $workspaceData['model']['softwareSystems'] ?? [],
                fn ($system) => ($system['location'] ?? 'Internal') === 'External'
            );

            $modelJsonData = json_encode($workspaceData['model'] ?? [], JSON_PRETTY_PRINT);
            if ($modelJsonData === false) {
                throw new \RuntimeException("Failed to encode model data");
            }

            return [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Please perform a security review of the following C4 architecture:\n\n" .
                                    "**Workspace**: {$workspace->name}\n" .
                                    "**External Systems**: " . count($externalSystems) . "\n\n" .
                                    "Focus on identifying security considerations and potential vulnerabilities."
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'resource',
                                'resource' => [
                                    'uri' => "structurizr://workspace/{$workspaceId}/model",
                                    'mimeType' => 'application/json',
                                    'text' => $modelJsonData
                                ]
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "**Security Review Checklist:**\n\n" .
                                    "1. **Authentication & Authorization**:\n" .
                                    "   - How do users authenticate?\n" .
                                    "   - Are there proper authorization boundaries?\n" .
                                    "   - Is role-based access control (RBAC) implemented?\n\n" .
                                    "2. **Data Protection**:\n" .
                                    "   - Where is sensitive data stored?\n" .
                                    "   - Is data encrypted in transit and at rest?\n" .
                                    "   - Are there proper data validation mechanisms?\n\n" .
                                    "3. **External Interactions**:\n" .
                                    "   - How are external systems accessed?\n" .
                                    "   - Are API keys/credentials properly managed?\n" .
                                    "   - Is there proper input validation for external data?\n\n" .
                                    "4. **Security Boundaries**:\n" .
                                    "   - Are trust boundaries clearly defined?\n" .
                                    "   - Is there defense in depth?\n" .
                                    "   - Are containers properly isolated?\n\n" .
                                    "5. **Common Vulnerabilities**:\n" .
                                    "   - OWASP Top 10 considerations\n" .
                                    "   - Injection risks\n" .
                                    "   - XSS/CSRF vulnerabilities\n" .
                                    "   - Insecure direct object references\n\n" .
                                    "6. **Recommendations**:\n" .
                                    "   - Security improvements to implement\n" .
                                    "   - Missing security controls\n" .
                                    "   - Priority of security enhancements\n\n" .
                                    "Please provide specific findings for each area based on the architecture model."
                            ]
                        ]
                    ]
                ]
            ];
        } catch (WorkspaceNotFoundException $e) {
            $this->logger->error('Workspace not found for security review', [
                'workspaceId' => $workspaceId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Suggest improvements for the architecture
     *
     * This prompt analyzes the workspace and provides actionable suggestions for:
     * - Scalability improvements
     * - Maintainability enhancements
     * - Performance optimizations
     * - Documentation gaps
     *
     * @return array{messages: array<int, array{role: string, content: array<int, array{type: string, text?: string, resource?: array{uri: string, mimeType: string, text: string}}>}>}
     */
    #[McpPrompt(
        name: 'suggest_improvements',
        description: 'Suggest architectural improvements for scalability, maintainability, performance, and documentation quality'
    )]
    public function suggestImprovements(
        #[Schema(description: 'Workspace ID to analyze for improvements', minLength: 1, maxLength: 50)]
        string $workspaceId,
        #[Schema(
            description: 'Focus area for improvement suggestions',
            enum: ['scalability', 'maintainability', 'performance', 'documentation', 'all']
        )]
        string $focusArea = 'all'
    ): array {
        $this->logger->info('Generating improvement suggestions prompt', [
            'workspaceId' => $workspaceId,
            'focusArea' => $focusArea
        ]);

        try {
            $workspace = $this->workspaceManager->load($workspaceId);

            $workspaceData = $workspace->toArray();

            $focusDescription = match ($focusArea) {
                'scalability' => 'Focus specifically on scalability improvements (horizontal/vertical scaling, caching, load balancing)',
                'maintainability' => 'Focus on maintainability (code organization, modularity, documentation)',
                'performance' => 'Focus on performance optimizations (latency, throughput, resource usage)',
                'documentation' => 'Focus on improving architecture documentation quality',
                default => 'Provide comprehensive suggestions across all areas'
            };

            $jsonData = json_encode($workspaceData, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                throw new \RuntimeException("Failed to encode workspace data");
            }

            return [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Please analyze the following architecture and suggest improvements:\n\n" .
                                    "**Workspace**: {$workspace->name}\n" .
                                    "**Focus**: {$focusDescription}\n\n" .
                                    "Provide specific, actionable recommendations."
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'resource',
                                'resource' => [
                                    'uri' => "structurizr://workspace/{$workspaceId}",
                                    'mimeType' => 'application/json',
                                    'text' => $jsonData
                                ]
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $this->getImprovementPromptText($focusArea)
                            ]
                        ]
                    ]
                ]
            ];
        } catch (WorkspaceNotFoundException $e) {
            $this->logger->error('Workspace not found for improvement suggestions', [
                'workspaceId' => $workspaceId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get improvement prompt text based on focus area
     */
    private function getImprovementPromptText(string $focusArea): string
    {
        $commonText = "**Please analyze and provide:**\n\n";

        return $commonText . match ($focusArea) {
            'scalability' => "1. **Horizontal Scaling**: Which components can be scaled horizontally?\n" .
                "2. **Bottlenecks**: Identify potential scalability bottlenecks\n" .
                "3. **Caching Strategy**: Where can caching improve scalability?\n" .
                "4. **Load Balancing**: Recommendations for load distribution\n" .
                "5. **State Management**: How to handle stateful vs stateless components\n" .
                "6. **Database Scaling**: Database sharding, replication, or partitioning strategies\n" .
                "7. **Async Processing**: Opportunities for asynchronous operations\n" .
                "8. **Resource Limits**: Capacity planning considerations\n\n" .
                "Prioritize recommendations by impact and implementation effort.",

            'maintainability' => "1. **Modularity**: Are components properly separated by concern?\n" .
                "2. **Coupling**: Identify tight coupling that could be loosened\n" .
                "3. **Cohesion**: Are related functions grouped together?\n" .
                "4. **Naming**: Are element names clear and consistent?\n" .
                "5. **Documentation**: What documentation is missing or unclear?\n" .
                "6. **Dependencies**: Are dependency directions appropriate?\n" .
                "7. **Technical Debt**: Identify areas of technical debt\n" .
                "8. **Testing Strategy**: Recommendations for testability\n\n" .
                "Focus on long-term maintainability and developer experience.",

            'performance' => "1. **Latency**: Where can response times be improved?\n" .
                "2. **Throughput**: How to handle higher request volumes?\n" .
                "3. **Database Queries**: Potential query optimizations\n" .
                "4. **Network Calls**: Reduce unnecessary network hops\n" .
                "5. **Caching**: Strategic caching opportunities\n" .
                "6. **Resource Usage**: Memory and CPU optimization areas\n" .
                "7. **Batch Processing**: Where to use batch vs real-time processing\n" .
                "8. **CDN Usage**: Content delivery optimizations\n\n" .
                "Prioritize by expected performance impact.",

            'documentation' => "1. **Element Descriptions**: Which elements need better descriptions?\n" .
                "2. **Missing Views**: What C4 views should be added?\n" .
                "3. **Relationships**: Are relationship descriptions clear?\n" .
                "4. **Decision Records**: What ADRs should be documented?\n" .
                "5. **Deployment**: Is deployment topology documented?\n" .
                "6. **Technology Choices**: Are technology decisions explained?\n" .
                "7. **View Quality**: How to improve existing views?\n" .
                "8. **Onboarding**: What would help new team members?\n\n" .
                "Focus on making the architecture understandable.",

            default => "1. **Scalability**:\n" .
                "   - Horizontal scaling opportunities\n" .
                "   - Potential bottlenecks\n" .
                "   - Caching strategies\n\n" .
                "2. **Maintainability**:\n" .
                "   - Modularity improvements\n" .
                "   - Coupling reduction\n" .
                "   - Documentation gaps\n\n" .
                "3. **Performance**:\n" .
                "   - Latency optimizations\n" .
                "   - Database query improvements\n" .
                "   - Resource usage reduction\n\n" .
                "4. **Documentation**:\n" .
                "   - Missing descriptions\n" .
                "   - Additional views needed\n" .
                "   - ADR suggestions\n\n" .
                "5. **Best Practices**:\n" .
                "   - C4 model best practices\n" .
                "   - Industry standards compliance\n" .
                "   - Common anti-patterns to avoid\n\n" .
                "Provide top 10 prioritized recommendations with estimated effort and impact for each."
        };
    }
}
