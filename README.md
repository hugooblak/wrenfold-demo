# Wrenfold Joinery — WordPress recovery and hardening demo

A live demo built for an Upwork job: a WordPress site that had been through a malware
clean-up and was left with broken styling, five overlapping page-builder add-on packs,
two different builders across its pages, and a request to make it work better on phones.

That job is not a "build me a website" job, so this is not a brochure site pretending to
be one. It is a small business site plus **Site Triage**: an admin toolkit that reads the
live install and answers the four questions the client actually asked.

**Wrenfold Joinery is a fictional brand.** The company, the people, the address and the
history are invented. Nothing here imitates a real business.

## Open it

[Open the demo in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/hugooblak/wrenfold-demo/main/blueprint.json)

It builds a fresh WordPress in the browser, logs you in as an administrator and loads the
demo content. Look for **Site Triage** near the top of the admin menu.

## What Site Triage does

| Screen | The question it answers |
|---|---|
| Overview | How much is wrong, and where to start |
| Security | What the clean-up left behind, with the evidence for each finding |
| Plugin audit | Which add-on packs can be removed without emptying a page, including Elementor widget packs that register no blocks at all |
| Builders | Which builder made which page, and whether standardising is worth it |
| Mobile | What in the saved content breaks on a narrow screen |
| | and which plugins cost every visitor a file while being used on one page or none |
| Unstyled buttons | Why a styled button renders as a plain link, and how to find the cause |
| Fix log | Every change the tool made, who made it, and how to undo it |
| How this demo was set up | Every problem planted on purpose, listed in full |

No finding on those screens is written down in advance. Press **Run a scan now** and every
number is read from the install at that moment and stored as one scan. The public
`/site-health-report/` page shows that stored scan, with the date it was taken, and leaves out
the evidence column: account names, file paths and plugin versions are exactly what somebody
probing a site wants, and a page about hardening should not publish them.

## Honesty

The install is deliberately broken so the scan has something real to find: a PHP file in
the uploads folder, a scheduled job with no code behind it, a second administrator
account, open registration, a stray file in the WordPress folder, a stylesheet handle
pointing at a file that does not exist, three overlapping add-on plugins (one of which loads a
stylesheet on every page view and is used nowhere), leftover builder fields on two live pages,
two draft pages whose layout really does live in Elementor and BeBuilder data instead of in the
page text, and an old homepage section kept the way a migration usually keeps it.

**No malicious code was written for this demo.** The file planted in the uploads folder
contains a comment and nothing else. The check is about a runnable file being in a folder
meant for pictures, not about what is inside it.

Two checks that belong in a real engagement are **not** here. There is no comparison of core
and plugin files against the copies WordPress published, because that needs to reach
wordpress.org, and no rendered-page measurement on the mobile audit, which reads saved content
only. Both are said out loud on the screens they belong to.

Site Triage does not remove malware, and it does not claim to. Getting rid of an active
infection means restoring from a backup taken before it started and rotating every
password and key. This tool finds what a clean-up left behind, explains it and closes the
doors.

BeTheme, Elementor, Slider Revolution and the commercial add-on packs are not installed
here. They are licensed software and cannot go in a public repository.

## How it is built

- Native WordPress blocks, a block theme, `theme.json` and patterns. No page builder.
- The FAQ library and Site Triage are separate plugins, so content and tools survive a
  theme change.
- The public site health report is a dynamic block, so the page is editable like any other.
- Fixes are opt-in, one at a time, logged, and reversible.

## Tests

`proof/README.md` holds the result of the last full run: markup lint, pages, link crawl,
editor validity, editor font sizes, axe at two widths, keyboard, forms, layout from 320px
to 1440px, Lighthouse and screenshots.
