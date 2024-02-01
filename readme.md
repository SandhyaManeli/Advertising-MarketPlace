## Using Issue Tracker ##
Issues or feature requests will be created here. The lifecycle of issue goes like following:

* issue created
* dev is assigned(by admin), and assigns label `Pending from dev`
* dev codes, completes it, pushes the branch and creates a pull request, comments about the same on the issue and changes the label to `Pending for testing`.
* moves to testing
  * if issues are found, the issue goes back to dev. the tester must comment issues found in issue thread, remove the label `Pending for testing` and attach `Pending from dev`.
  * if no issues are found, the issue goes to code reviewer. The tester should comment it on the issue thread, remove the label `Pending for testing` and attach `Pending for code review`.
* moves to code review
  * if issues are found in code review, the issue goes back to dev. The reviewer must comment the issues found in issue thread and **code review**. Also, the label `Pending for code review` will be removed and `Pending for dev` will be attached.
  * if no issues are found the issue is closed and merged with main branch. also the code will be moved to production. all the labels will be removed.

At any point in the life-cycle of an issue, for any change we have to update the status in comments on issue thread so to make sure everyone knows the status/requirements/complications involved.
