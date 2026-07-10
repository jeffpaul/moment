# 00_README.md addendum — Claude Code + Fable Build Workflow

Add this section to `00_README.md` immediately after the existing
"How to use this package" section. It replaces step 4 in that section
(the original `05_llm_prompt_build_prototype.md` reference).

---

## Building the prototype with Claude Code

This package includes a complete Claude Code setup for building the Moment
prototype using Fable-class models with multi-agent orchestration.

### Package structure for Claude Code

```
project-moment/               ← artifact and context docs (this package)
  00_README.md
  02_one_page_product_brief.md
  04_prototype_mvp_spec.md
  05_llm_prompt_build_prototype.md          ← original single-pass prompt
  05_llm_prompt_build_prototype_claude_code.md  ← Claude Code orchestration prompt (use this)
  08_decisions_and_open_questions.md
  09_default_syndication_routing.md
  11_conversation_backflow_notifications.md
  12_content_model_technical_path.md
  13_success_metrics_and_e2e_tests.md
  ...

CLAUDE.md                     ← place in WordPress root (same level as wp-config.php)

.claude/                      ← place in WordPress root
  agents/
    wp-php-core.md            ← PHP + REST API + publisher specialist
    moment-frontend.md        ← mobile app shell + CSS + JS specialist
    moment-syndication.md     ← connector registry + routing specialist
    moment-backflow.md        ← backflow import + notifications specialist
    moment-tester.md          ← PHPUnit + WP-CLI + Playwright specialist
```

### Setup (one-time)

1. Clone or copy the `project-moment/` artifact directory somewhere accessible.

2. Copy `CLAUDE.md` into your **WordPress installation root** (same directory as `wp-config.php`):
   ```bash
   cp project-moment/CLAUDE.md /path/to/your/wordpress/
   ```

3. Create the `.claude/agents/` directory in your **WordPress root** and copy the agent files:
   ```bash
   mkdir -p /path/to/your/wordpress/.claude/agents
   cp project-moment/claude-agents/*.md /path/to/your/wordpress/.claude/agents/
   ```

4. Start Claude Code from your **WordPress root**:
   ```bash
   cd /path/to/your/wordpress
   claude --model claude-fable-5
   ```
   If Fable is not yet available via your API access, Opus 4.8 also works well with this structure.

5. Paste the contents of `05_llm_prompt_build_prototype_claude_code.md` as your first message.

### How the multi-agent build works

The orchestration prompt loads all context documents into Fable's 1M token window,
then delegates each build phase to a specialist sub-agent:

| Phase | Sub-agent | What it builds |
|-------|-----------|----------------|
| 0 | Orchestrator | Environment checks + CLAUDE.md verification |
| 1 | `wp-php-core` | Plugin scaffold + activation |
| 2 | `wp-php-core` | REST API + publisher class |
| 3 | `moment-frontend` | Mobile app shell + all 6 screens |
| 4 | `wp-php-core` | AI Assist adapter (WP 7.0 + mock) |
| 5 | `moment-syndication` | Connector registry + routing |
| 6 | `moment-backflow` | Backflow import + notifications endpoint |
| 7 | `moment-frontend` or `wp-php-core` | Blocks / shortcodes |
| 8 | `moment-frontend` | PWA manifest + service worker |
| 9 | `moment-tester` | PHPUnit + WP-CLI smoke + Playwright scaffold |

Each phase has a bash verification gate. The next phase does not start until the gate passes.

### Which prompt to use for which situation

| Situation | Use |
|-----------|-----|
| Full autonomous prototype build with Claude Code + Fable | `05_llm_prompt_build_prototype_claude_code.md` |
| Single-session quick prototype with Cursor, OpenClaw, or another agent | `05_llm_prompt_build_prototype.md` |
| Continuing a specific phase in a new Claude Code session | Start with CLAUDE.md in context, describe the current phase status |

### Resuming a session

If Claude Code compacts context or you start a new session, Claude Code will
re-read `CLAUDE.md` automatically. The phase status checklist in `CLAUDE.md`
tells the new session exactly where work stopped.

To resume from a specific phase:
```
Resume Project Moment from Phase [N]. CLAUDE.md has current status.
Load context documents from project-moment/ before continuing.
```

### Note on Fable 5 / Claude Mythos availability

As of July 2026, Claude Mythos / Fable-class models are available through
Anthropic's Project Glasswing for select organizations. The orchestration
structure in this package works with any sufficiently capable Claude model.
Opus 4.8 is the recommended fallback. The `--model` flag may vary based on
your access level — check current Anthropic API documentation for available
model strings.
