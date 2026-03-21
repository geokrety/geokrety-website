---
name: unit-test
description: 'Unit test skill for testing using phpunit wrapper.'
user-invocable: true
---

# Unit Test Skill
## Purpose
- Provide clear, unambiguous instructions for invoking the PHPUnit wrapper shipped with this skill.

## Wrapper script (where it lives)
- The wrapper script for this skill is located in the same directory at #file:./scripts/phpunit.sh (relative to this #file:/SKILL.md ). To avoid ambiguity, use the explicit repository-root path when invoking it from automation or CI:

- From the repository root:

```bash
cd ${workspace} # ensure you're at the repo root
./.github/skills/unit-test/scripts/phpunit.sh
```

- Or, if you `cd` into the skill directory first:

```bash
cd ${workspace}/.github/skills/unit-test/scripts/ # ensure you're at the repo root
./phpunit.sh <command> [args...]
```
