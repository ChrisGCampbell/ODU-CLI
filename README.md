# ODU-CLI
## Web-based interface for the definition of, and the reporting on Action Items (AIs). - PHP, XML, JavaScript, HTML5

### Description: 
Web-based interface for the definition and management of Action Items

#### Functionality:
The file actionitems.xml starts with <ACTIONITEMS> and ends with </ACTIONITEMS>. Each action item entry is encapsulated in <Actionitem> and </Actionitem>. The following attributes are available:
<ID> Unique system-generated identification of the AI
<GROUP> Group name of parent incident: Project, Workpackage, Task, Meeting, Individual (list can change over time)
<PID>Id of parent incident in the group
<NUMBER> Sequential number of AI for this parent
<AIACRO> This is an acronym for the AI with the format: PAR-XXX-Number where PAR is derived from the Group of the parent incident, XXX is derived from the parent incident, and Number if the AI number.  
<OWNER> person id of the owner of the AI; normally the person who established the AI
<ACCESS> Access rules; the rules still have to be defined
<RESPONSIBLE> person id of the person, or a group of persons, to execute the AI
<CREATED> Date the AI was created
<DEADLINE> Date the AI is expected to be finished
<DEPENDENCY> Any dependency on other AIs; could be more than one AI ID
<DESCRIPTION> Description of the AI
<RATIONALE> Reason for the AI


The file aireports.xml starts with <AIREPORTS> and ends with </AIREPORTS>. Each report entry is encapsulated in <Aireeport> and </Aireport>. The following attributes are available:

<ID> Unique system-generated identification of the report
<AIID> Identification of the AI this report belongs to
<OWNER> Person id of the person reporting on the AI
<DATE> Date and time the report was published
<REPORT> The actual report
<NDESCRIPTION> Updated description
<NDEADLINE> Updated deadline
<NRESPONSIBLE> Updated responsible persons
<STATUS> Status of the AI: Open, Closed, Done, Obsolete, (more can be added later) 


We need the following web pages:

(1) Publish new AI/edit AI: the page should first ask for inputs of Group and Group incident. You should read the groups from the file known_groups.txt. Initially this file will contain:
------
7
Project 
Workpackage 
Task 
Milestone
Deliverable
Meeting 
Individual
-------

(2) The known group incidents should be stored in a separate file, e.g. known_incidents.txt. Whenever a new incident is entered after a group has been selected, you should add this incident into known_incidents.txt together with the group it belongs to. The contents of known_incidents.txt should be in the format:
----------
Project|NSF20160001
Workpackage|NSF20160001-WP1
Task|NSF20160001-WP1-T2
Milestone|NSF20160001-WP1-T2-M1
Deliverable|NSF20160001-WP1-D3
Meeting|5thGSTSWS
----------

(3) Publish new Report/edit Report: after a user has selected a Group the user should see all incidents in that group and, after selecting an incident see all the AI from this particular incident. After selecting a specific AI, the user should see the full AI plus all previous reports and then be able to add a new report. The user also should be able to edit any of the reports the user has published previously but not any of the reports that were published by others.
 
(4) View AIs: after a user has selected a Group the user should see all incidents in that group and, after selecting an incident see all the AI from this particular incident. After selecting a specific AI, the user should see the full AI plus all previous reports and then be able to add a new report. This is very similar to what is needed for (2).