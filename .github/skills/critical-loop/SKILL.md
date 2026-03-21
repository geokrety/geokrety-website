---
name: critical-loop
description: 'Create a critical-thinking loop for the current work.'
user-invocable: true
---

# Critical Loop Skill

## Purpose

Scaffold, validate, review, and apply/test the current user request. This skill orchestrates the entire agent calling lifecycle: calling #agent dba -> debug -> performance-engineer -> quality-engineer -> refactoring-expert -> critical-thinking to iteratively refine the solution until it meets the user's needs.

## When to Use This Skill

- User asks to "use critical-thinking loop", "run critical review loop", "enter critical loop", "usual thinking loop", or similar phrases indicating they want a comprehensive, iterative approach to a complex task
- Task involves multiple steps that require validation and expert review
- Any task that involves evolving the GeoKrety website PostgreSQL schema
- When exhaustive regression tests are needed alongside a schema change

### Expert Review Loop

Invoke the following agent loop. **Maximum 5 full rounds**; if consensus is not reached by then, record remaining concerns in `${workspace}/99-OPEN-QUESTIONS.md` and proceed. The **human user has final authority** on any unresolved concern. At the end of each round, check the updated files again as the user may have added `TODO` comments in the updated files to guide the next round of review, you must read those comments and verify they are addressed in the next round. Also re-reader this `SKILL.md` documentation after each round to ensure all best practices are followed as there may have been live updates to the documentation.

## General Step-by-Step Workflow

1. **Initial Analysis**
    - Read the user's request carefully
    - Identify the main goal and any specific requirements
    - Determine which experts (DBA, Debug, Performance Engineer, Quality Engineer, Refactoring Expert) need to be involved, generally all of them for database-related tasks

2. **DBA Phase**
    - Generate SQL code for schema changes, migrations, or backfill scripts
    - Ensure code follows best practices for PostgreSQL and GeoKrety's architecture
    - Provide explanations for each change and how it fits into the overall goal

3. **Debug Phase**
    - Review the generated SQL for potential issues, edge cases, and unintended consequences
    - Simulate the execution of the SQL to identify any logical errors or performance bottlenecks
    - Refine the SQL based on findings

4. **Performance Engineer Phase**
    - Analyze the SQL for performance implications, especially on large datasets
    - Suggest optimizations such as indexing strategies, batch sizes, or query restructuring
    - Provide benchmarks or estimates for execution times

5. **Quality Engineer Phase**
    - Design comprehensive tests to validate the correctness of the SQL changes
    - Ensure tests cover edge cases and potential failure points
    - Provide instructions for running the tests and interpreting results

6. **Refactoring Expert Phase**
    - Review the SQL for maintainability and readability
    - Suggest improvements to make the code cleaner and easier to understand
    - Ensure that the code adheres to any style guides or conventions used in the GeoKrety codebase

7. **Check for User Comments**
      - After each round, check if the user has added any comments or `TODO` notes in the updates files after the previous rounds of review.
      - If yes, verify that the concerns raised in those comments are addressed in the next round of review.

8. **Critical Thinking Phase**
    - Review the entire solution holistically, ensuring it meets the user's original requirements and is robust against potential issues
    - Consider any edge cases or scenarios that may not have been covered in previous phases
    - Provide a final assessment of the solution's readiness for production deployment

9. **Final Review**
    - Pass unresolved concerns back through agents up to the 5-round cap.
    - If any concern remains, file it in `${workspace}/99-OPEN-QUESTIONS.md`, add a cross-reference comment in the relevant file, and proceed
    - open questions do **not** block the full review process or database migration apply but must be reviewed before the next production deployment.
