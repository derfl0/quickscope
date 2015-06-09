# Quickscope
A fast information tool for studip links

## How to extend

### Add a new linktype

To add a new linktype only add a new json object in assets/quickscope.js to STUDIP.quickscope.hooks

Example
{type: 'course', searchstring: 'details.php', idregex: /sem_id=(.[^\&]*)/i}

type = the action which should be called in request
searchstring = the string the link must contain to apply the quickscope
idregex = regex to find the id that is given as first parameter to the action

### Add a new / modify quickscope

The action must always returns a json object. 

{
avatar: link to the avatar
header: title of the object
text (array): normal texts in quickscope
important (array): important (bold) texts in quickscope
error (array): error (red) texts in quickscope
action (array): rightclickmenu
{
    label: text of menupoint
    icon: iconurl of menupoint
    url: url to call on click
    type (optional): 'dialog' if action should not be loaded quiet in background but shown in a dialog
}
}
