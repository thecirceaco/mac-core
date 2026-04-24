# MAC Core -> MAC Docs Sync Map

Use this note whenever a `MAC Core` change may require updates in `mac-docs`.

## First Places To Check

- Official product docs:
  - `D:\business\projects\mac-docs\sources\doc\mac-core\`
- Official release history:
  - `D:\business\projects\mac-docs\sources\log\mac-core\`
- Starter integration docs:
  - `D:\business\projects\mac-docs\sources\doc\mac-starter\plugins\mac-core\index.md`
- Starter behavior docs when the change affects default build conventions:
  - `D:\business\projects\mac-docs\sources\doc\mac-starter\posts\`
- Known ownership and duplicate-feature pages when the change overlaps other plugins:
  - `D:\business\projects\mac-docs\sources\doc\mac-starter\plugins\perfmatters\index.md`
  - `D:\business\projects\mac-docs\sources\doc\mac-starter\plugins\patchstack\index.md`

## Search Command

Use this repo search to find additional references before finishing the task:

```powershell
rg -n "mac-core|MAC Core" D:\business\projects\mac-docs\sources
```

## When A Docs Review Is Usually Required

Review `mac-docs` in the same task when a `MAC Core` change affects:

- public settings or defaults
- settings-driven policies
- admin page behavior or navigation
- starter recommendations
- reusable utility APIs or extension hooks
- licensing or release behavior visible to builders

If the change ships in a formal release, also update the matching `MAC Core` release-log version page and keep `date` frontmatter on that version page.

If the change is internal-only and does not affect any of the areas above, a full `mac-docs` update may not be needed.
