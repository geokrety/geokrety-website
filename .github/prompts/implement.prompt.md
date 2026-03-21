---
agent: 'GPT 5 Beast Mode'
tools: [vscode/askQuestions, execute/runInTerminal, read/problems, read/readFile, agent, edit/createFile, edit/editFiles, search/changes, search/codebase, search/fileSearch, search/listDirectory, search/textSearch, postgresql-mcp/pgsql_connect, postgresql-mcp/pgsql_disconnect, postgresql-mcp/pgsql_list_databases, postgresql-mcp/pgsql_query]
description: 'request implementation of a feature or fix defined in a file.'
---
<!-- model: 'GPT-5.4' -->

# Hard requirements

- you MUST implement ALL the points or fix defined in the file provided in the context.
- you MUST create a "working folder" in #file:../../tmp/xxx/ where `xxx` is a unique identifier for this task.
- you MUST use this "working folder" to put all your temporary files, including code snippets, test cases, and any other relevant information you need to complete the task.
- you CAN access the running database to query for any relevant information that can help you in the implementation process using #pgsql_query tooling or `psql` (using default connection parameters `geokrety` database, `geokrety` user, `geokrety` password, `localhost` host, `5432` port).
- you MUST write code that is consistent with the existing codebase in terms of style, structure, and conventions.
- you MUST write tests for your implementation to ensure that it works correctly and does not introduce any regressions.
- if a #file:../../tmp/xxx/specs.md file is missing, using the skill `critical-thinking` (with agents `requirements-analyst`, `quality-engineer`, `specifications`, `critical-thinking`, `technical-writer`), you MUST create one based on the requirements defined in the file provided in the context and any additional information you can gather from the codebase or database. This specs file should include a clear definition of the feature or fix, the expected behavior, and any relevant edge cases or constraints.
- if a #file:../../tmp/xxx/specs.md file is provided (or just created), you MUST follow the specifications defined in that file for your implementation.
- you MUST document your code and the implementation process clearly and thoroughly in the #file:../../tmp/xxx/implementation.md file, including any assumptions you made, the rationale behind your decisions, and any trade-offs you considered.
- you MUST create a file named #file:../../tmp/xxx/tasks.md in the "working folder" where you will document your implementation process with advancement entries and checkboxes as work proceeds.
- once you have completed the implementation, you MUST review your work to ensure that it meets the requirements and is of high quality before `git commit` it.
- once you have completed the implementation, you MUST use the "critical-loop" skill on agents "specification" -> "technical-writer" -> "requirements-analyst" to review your implementation and ensure that it meets the requirements and is of high quality.
- once a "user input" task from the #file:../../tmp/xxx/tasks.md file is read and understood, you MUST immediately tick the corresponding checkbox and include it in the current process. If the spec need updates, you MUST update the spec accordingly and restart the process where appropriate to properly reflect the changes.
- once a "user input" task from the #file:../../tmp/xxx/tasks.md file is complete you MUST tick the corresponding checkbox.

## Important and mandatory notes

- you MUST read the #file:../../tmp/xxx/user-inputs.md every time before making an advancement entry in the #file:../../tmp/xxx/tasks.md file to ensure that you are aligned with the *live* user's expectations and requirements.
- you MUST read the #file:../../tmp/xxx/user-inputs.md after each agent iteration to check for any updates or changes in the user's requirements or expectations and adjust your implementation process accordingly.
- do not stop on Open Questions, you MUST make assumptions and move forward with the implementation, but you MUST document these assumptions in the #file:../../tmp/xxx/implementation.md file.

# Sample #file:../../tmp/xxx/user-inputs.md

```
# User Inputs

- [x] user want to add something...
- [ ] user want to add something else...

```

# Sample #file:../../tmp/xxx/implementation.md

```
# feature title

## Summary

- a brief summary of the feature or fix you implemented

## Status

complete/incomplete/blocked

## Progress

- [x] task 1 blablabla
- [ ] task 2
- [ ] task 3
...

## Notes

Any relevant notes about the implementation, such as assumptions made, rationale behind decisions, trade-offs considered, etc.

## Validation

- a list of tests you ran to validate your implementation, including any relevant details about the test cases and their results.

## Open Questions

- a list of any open questions or uncertainties that arose during the implementation process, along with any assumptions you made to move forward.

## Next Steps

- a list of any next steps or follow-up tasks that need to be done after the implementation, such as code review, documentation, deployment, etc.

```
