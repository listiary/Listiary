const options = {

    // Do we load editor or a viewer
    ShowEditor: false,

    // For censoring items by id
    CensoredNamespaces: [], //"radiowatch.more"



    // Wether we will be using local js file as source / payload (for debugging purposes)
    FetchLocal: false,

    UseHeaderStripColors: true,
    HeaderStripColors: Object.freeze({

        //https://www.w3schools.com/colors/colors_picker.asp
        Public: "#99b3ff",
        Personal: "#00cc00",
        Private: "#ff66d9",
        Paid: "#ff3333",
        Normative: "#ffd633" // or "#ffcc00"
    })
};
