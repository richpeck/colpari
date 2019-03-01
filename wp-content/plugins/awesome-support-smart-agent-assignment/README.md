# Awesome Support: Smart Agent Assignment

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/b/awesomesupport/smart-agent-assignment/badges/quality-score.png?b=master&s=32edd8f4102d7fb213cec356c405f72884b53388)](https://scrutinizer-ci.com/b/awesomesupport/smart-agent-assignment/?branch=master)

Automatically assign newly opened tickets to agents using a set of rules. There are 5 sets of rules to choose from:

**1. Product And Agent Availability #1**
1. Check for a set of agents that supports the product the user selected on the ticket and that has working hours (based on the day and times set in their agent/user profile)
2. If no match is found, then use the default agent (any agent with the least number of open tickets).
3. If no product is entered then check for any agent with availability.
4. If no match then use the default agent (any agent with the least number of open tickets).

**2. Product And Agent Availability #2**
1. Check for a set of agents that supports the product the user selected on the ticket. Then, from that set of agents, check for an agent currently working (based on the day and times set in their agent/user profile). 
2. If an agent isn't found, then check for any agent assigned to that product regardless of working hours. 
3. If one is not found then check for any agent with current working hours regardless of product. 
4. If an agent is still not found then assign the ticket to the default agent (any agent with the least number of open tickets). 
5. If no product is entered on the ticket then check for any currently working agent agent. If no agent is found then assign to the default agent.

**3. Departments And Agent Availability #1**
1. Try to find a current working agent who has the same department as the ticket. 
2. If no match then use the default agent (any agent with the least number of open tickets).

**4. Departments And Agent Availability #2**
1. Try to find a current agent who has the same department as the ticket. 
2. If no agent is found then use any available agent regardless of department. 
3. If an agent is still not found then use the default agent (any agent with the least number of open tickets).

**5. Agent Availability #1**
Check for a set of agents based on agents day and time availabilty only. If no agents exist use the default agent (any agent with the least number of open tickets).

**6. Product And Agent Availability #3**
1. Check for a set of agents that supports the product the user selected on the ticket and that has working hours (based on the day and times set in their agent/user profile)
2. If no match is found, then use the default agent (any agent with the least number of open tickets).
3. If no match then use the default agent (any agent with the least number of open tickets).


## Getting Started

Installation is straightforward using the usual WordPress Plugins -> Add New procedure.

- Download [Smart Agent addon](Awesome-Support-Smart-Agent-Assignment.zip).
- Within WordPress Dashboard, click `Plugins` -> `Add New`
- Click the `Upload` button and select the ZIP file you just downloaded.
- Click the `Install` button

## Usage

Once installed, fill out the new working hours fields in the agent profile along with the fields that tell AS which agents are responsible for which products. If using departments then make sure you assign agents to departments (in the standard USER PROFILE wordpress screen).  Then, enter tickets as usual.

### Change Log

-----------------------------------------------------------------------------------------
###### 2.4.0
New: Added option to control which roles can set agent availablity

###### 2.3.0
New: Added a sixth algorithm that is a variation on algorithm #1.

###### 2.2.0
New: Added option to prevent the algorithms from running for specified channels.  This allows you to run it for normal tickets but let things like Gravity Forms or Emails assign their own agents without fear of being overwritten by this module.
New: Added internal support for a smart-agent-bypass flag.  If a metavalue is set to 1 or true in a meta field called _wpas_bypass_smart_agent on the ticket, the smart agent algorithm is not run.
New: Added internal support for an algorithm override flag on the ticket.  If a metavalue is set in a meta field called _wpas_smart_agent_algo_override, that value will be used as the algorithm value ( currently valid values are 1 - 5 )

###### 2.1.0
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
! This version is a BREAKING change if you are running on multisite. 
! Your user settings for this add-on will be restored to their defaults.
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
Fix: Settings and values that should be unique to each user on each site were global to all sites for the same user when running on multi-site (replaced update_user_meta function calls with update_user_option function calls)
Fix: Some settings logic was not checking for empty arrays before attempting to use the array.
Fix: Multi-site logic fix - Original logic assumed the users table was always going to be prefixed by 'wpdb->prefix' but users table is prefixed with 'wpdb-base_prefix'.
Fix: Multi-site logic fix - Settings and values that should be unique to each user on each site were global to all sites for the same user when running on multi-site (replaced update_user_meta function calls with update_user_option function calls)

###### 2.0.2
Tweak: Forwarded version number to force upgrades since some users might be running early versions of 2.0.1 

###### 2.0.1 
1. Algorithm tweaks
2. Various code clean up and tweaks
3. Added option to explicitly use the core round-robin algorithm
3. Fixed translation strings
5. Changed action hook used to calculate and stamp ticket with agent - used a later hook.
6. Update license warning messages to show the name of the plugin.
8. Fix license label not showing up in license screen.

###### October 31st, 2016 V 2.0.1 beta 2
1. Fixed selection of agents for algorithm by introducing an option to decide which roles are agent roles for the selected algorithm.
2. Fixed ability to edit time fields.

###### September 7th, 2016 V 2.0.1 beta
1. Added in additional time fields (2 more sets for a total of 4).

###### August 29, 2016 V 2.0.0
1. Added in four more algorithms for assigning agents
2. Created a second set of time fields for agents to account for situations where agents might have long breaks
3. Misc cosmetic adjustments.

###### August 21, 2016 V 1.0.1

1. If "multiple products" isn't enabled in Tickets->Settings, then the "set Product" field in the users profile shouldn't be there.
2. Add the current time below the agent's availablity time so users can more easily figure out any timezone differences between their time zone and their hosting server.
3. Change the "Plugin Name" to Awesome Support: Smart Agent Assignment

-----------------------------------------------------------------------------------------