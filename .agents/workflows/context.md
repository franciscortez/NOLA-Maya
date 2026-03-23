---
description: Standardized workflow for agents to understand project context and follow architectural patterns.
---

# Agent Context & Compliance Workflow

This workflow ensures all AI agents maintain consistency with the project's architecture (Service Layer + DTOs) and track progress accurately in the documentation.

## 1. Initial Research & Context Loading
Before performing any technical task, you **MUST** read the following files:
- [task-progress.md](file:///Users/bamcortez/Documents/Code/NOLA-Maya/.agents/docs/task-progress.md) - To understand current progress and next steps.
- [context-codebase.md](file:///Users/bamcortez/Documents/Code/NOLA-Maya/.agents/docs/context-codebase.md) - To understand the architecture, database schema, and integration flows.

## 2. Architectural Alignment
All implementations must align with the **MVC + Service Layer + DTO** pattern defined in `context-codebase.md`.
- **Controllers**: Keep them thin; orchestrate requests and responses.
- **Services**: Contain business logic and API interactions.
- **DTOs**: Use strongly typed Data Transfer Objects for passing data between layers.
- **Models**: Handle database interactions only.

## 3. HighLevel Custom Payments Integration
Refer to HighLevel's official documentation for payload structures and handshake requirements:
- [HighLevel Custom Payments Doc](https://help.gohighlevel.com/support/solutions/articles/155000002620-how-to-build-a-custom-payments-integration-on-the-platform)

## 4. Documentation & Progress Tracking
- **task-progress.md**: After completing a feature chunk or a specific item, update its status from `[ ]` to `[x]`. 
- **context-codebase.md**: If you introduce new core services, schemas, or flows, update this file to reflect the new truth.

## 5. Testing Requirements
For **every** task or feature implemented, you **MUST** create corresponding tests in the [tests](file:///Users/bamcortez/Documents/Code/NOLA-Maya/tests) folder.
- **Unit Tests**: For individual services, DTOs, and utility logic.
- **Feature/Integration Tests**: For controller actions, API endpoints, and end-to-end flows.
- **Mocking**: Mock external APIs (Maya/GHL) to ensure tests are fast and reliable.

## 6. Execution Steps
1. **Analyze**: Read requirements and existing code.
2. **Plan**: Create/update an implementation plan artifact.
3. **Confirm**: Get user approval if the change is significant.
4. **Implement**: Write code following the architectural patterns.
5. **Test**: Create and run tests in the `tests` folder. Ensure all tests pass before proceeding.
6. **Verify**: Manually verify the changes and update documentation.
7. **Report**: Update `task-progress.md` and notify the user.
