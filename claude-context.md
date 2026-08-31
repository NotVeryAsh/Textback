# Claude Context

This is just a document on the best way to get Claude to do something in a systematic and consistent way

## 1. Claude.md

Claude references this file as the first step when you give it any prompt.
You can give it basic instructions here, or tell it to reference & load other .md 'context' files

In claude.md
> Load the following context files: editing-guidelines.md, frequent-used-tools.md, base-context.md, previous-projects.md

Context files are just memories and guidelines

### editing-guidelines.md example

```
These guidelines are universal to all editing software and must be following strictly- no deviations.

1. transitions
    - transitions always fade to black and take 2s to complete
    ...
    
2. font
    - the font family is 'Inter'
    - the font size is 16px
    - the line height is 24px
    ...
```

### previous-projects.md example

```
All previous projects can be found in the 'projects' folder.
They are organized by Y-M-D date format.

- When coming across knowledge gaps or when product decisions are needed, first research pprevious projects to see if 
this challenge has been solved before. Use the solution that this project used. Only use solutions for projects that were
 nade in the past week.
```

### Building context files

Dump all your knowledge, used tools and style guides into claude and let it build the context for you.

> NOTE: Always use Fable 5 as a 'conductor' that launches opus 5 sub agents that do the work.
>
> Fable 5 is incredible at guiding other agents and huge tasks, but it's slow. Opus 5 is way faster and better at doing micro-tasks.

#### Example prompt

```
All my projects are stored in the 'projects' folder. The tools I used Adobe Premiere Pro, DaVinci Resolve, ...
my social media handles are @joshua_k_johnson on Twitter and @joshua_k_johnson on Instagram.

Get one opus 5 sub agent to study the styles and techniques used for every individual project.
I have 20 projects, so this would be 20 sub agents

Also get 3 opus 5 sub agents to study the tools I listed. These sub agents needs to research the most commonly used tools I have used
throughout all of my projects.

Also get 2 opus 5 sub agents to study each of my social media pages to study the language, hashtags and target audience of each page.

When the agents all come back, populate all of the context files (editing-guidelines.md, frequent-used-tools.md, base-context.md,
previous-projects.md) with the context they learned.
```

#### Extra lines for claude.md

Add these lines to the end of the claude.md file to help dynamically build context as you work

```
Whenever you are working on any prompt, get the key insights, techniques, styles, tools used, and context, and ask 
yourself if it would be beneficical to add to the other context files referenced in claude.md Be very strict on this,
only add essential context, and always ask me if I should add it first. Do not add anything by yourself unless explicitly asked.
```

## 2. Commands

Claude will reference everything in the claude.md file on every prompt so your commands can be slimmer.
Run everything as Fable 5- opus 5 can handle the smaller task, Fable 5 is the 'conductor'.

> Handoff files. 
> I reference handoff files below. Claude gets swamped with context and its decision making becomes bloated and less efficient overtime- it still remembers all the bad useless details and errors it made.
> Handoff files are just .md context files which contain the most important info.
> You pass the handoff file to a new agent and its decision making is pristine and it carries no useless info.

#### /edit-video

```
Get 10 opus 5 sub agents to research the brand *brand name*. Their main industry is *industry*. Their social media handles are *social media handles*.
The video should be *video description*

When the agents come back, make a new directory for this project located at /*brand name*-Y-M-D. This folder will contain the video, the plan for the video,
and any other context files, assets, video exports, and assets required for the video. Create a plan.md file in the directory with all of the data that the sub agents found about the brand.

Then, get 10 opus 5 sub agents to complete each segment / scene of the video. They should focus on one individual aspect of the video.

Put the final video in the 'videos' folder. It should be 30 seconds long and exported in multiple formats- a 4k raw version, 1080p for Instagram, ...

Every 5 minutes, check the status of the sub agents to check everything is working and running smoothly. Retry any failed tasks

When you're done, create a handoff.md file in the project directory which contains the current status and all the details of this project so I can pass it off to another agent.

Then spin up a Fable 5 session and immediately run /review-video *project directory*
```

#### /review-video

```
Gain context and clarity on *project directory* by researching the handoff.md file. Make sure you have full context before you start this task.

Get 10 opus 5 sub agents to review the video- each sub agent should scrutinize and review 3 seconds of the video. No 3 seconds of review should overlap- each sub agent needs to review individual parts of the video.
Strictly reference the style guides and editing guidelines of this project. Scrutinize anything that looks even remotely out of place. Treat every minor incorrect detail as a catastrophic failure.

Each sub agent should make a list of all of the flaws in the video.

When the agents come back, create a feedback.md file in the project directory, listing all of the flaws, their timestamps, what made them a flaw, and any extra details which are relevant.

Then get 10 more opus 5 sub agents to validate the flaws inside the feedback.md file. If there is anything standing and major after the agents review them, put the video back into the 'in progress' directory.

If a video has no flaws, put it into the 'review' directory, and send me a notification that I need to view the video.
```

... etc etc - you could add commands to generate captions, on-brand descriptions and titles, etc.

#### Posting on social media

You can connect integrations to claude to post on social media, or use an API- programming language doesn't matter- use whichever claude knows best

#### Stats and analytics monitoring

Connect claude to analytics integrations or APIs.

Then get claude to check analytics of all sorts overtime. Look at claude routines: https://code.claude.com/docs/en/routines - scheduled tasks

In a daily claude routine, you can get claude to check all the videos that are 7 days old, and gather data about their analytics-
 - Higher target audiences
 - Engagement stats
 - ...

Then save all of this data somewhere- in an analytics app or a database etc.

Then get another daily claude routine to email you data on the analytics you collect- trends over time, engagement growth, etc.
From here, you can get claude to analyze patterns and trends, see what works, etc, and build onto the context files you have, or create new ones.
