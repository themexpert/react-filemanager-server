## React File Manager (Chooser)

## Usage

#### Server side
Let's say we have the following file structure

* $_SERVER['DOCUMENT_ROOT']
    * app
        * FM
        * index.php
    * public
        * uploads

Change the File Manager mount root in `/app/FM/config.php` file 

```PHP
    'root' => "/public/uploads" //file manager will be mounted at this directory
```

File: `/app/index.php` 
```PHP
    require_once 'FM/FileManager.php';
    
    (new FileManager());
    exit; //just incase someone else tries to send some more data or maybe upto you
```

Now our server endpoint will be `/app/`

We can use any other file and have the server endpoint wherever we want


#### Client Side

`src/file-manager.jsx` is a React component, It takes a `string` prop, `server`.
```JSX
import FileManager from './src/file-manager.jsx';
....
<FileManager server={server_endpoint}/>
```

`src/index.js` is a wrapper on `src/file-manager.jsx` that returns a callback that mounts the `FileManager` component, appends to body and then returns a callback that can be used to open the file manager modal
```JS
import initFM from './src'

const openFileManager = initFM('server_endpoint');
```

The returned callback `openFileManager` accepts a `callback` as a parameter to show the file manager modal

```html
<button onclick="openFileManager(fileSelectCallback)">Open File Manager</button> 
```
```JS
function fileSelectCallback(path, selected_list) {
    console.log(path, selected_list);
    //do whatever
    
    //path => the relative path to the root path set in `app/FM/config.php`
    //selected_list => selected files and folders
    
    //return true to close the modal
    //return string to show info/error after validation
    
    //let's say we want user to select only one file
    if(selected_list.length !== 1) {
        return 'Only one file has to be selected';
    }
    //let's say the user selected a folder which is not good for us
    if(selected_list[0].is_dir) {
        return 'Only file can be selected';
    }
    
    //we got one file selected her
    // //do whatever
    
    return true; //close the modal
}
```


It's a good idea to make a wrapper to instantiate the file manager and the using it elsewhere

File: `wrapper.js`

Content: 
```JS
import initFM from './src'

export default initFM('server-endpoint');
```

Use the wrapper in any React component

```JS
import React, {Component} from 'react'
import openFileManager from './wrapper' //wherever it is

export default class FilePicker extends Component {
    constructor(props) {
        super(props);
        
        this.state = {
            filename: null
        };
    }
    
    onFileSelect = (dir, files) => {
        //we want the user to select at least one file
        if(!files.length)
            return 'Please select a file';
        
        //we want the user to select less or equal to one file
        if(files.length > 1) {
            return 'Only one file can be selected';
        }
        
        //which implies user selected one file by this line of code
        
        //relative path to the root of file manager set in the server side config
        const filepath = dir + files[0].basename;
        console.log(filepath, dir, files);
        
        return true; //closes the file manager modal
    };
    
    render = () => {
        return (
            <button onClick={openFileManager(this.onFileSelect)}>Pick File</button>
        );
    };
}
```