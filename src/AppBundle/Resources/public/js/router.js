var Router = Backbone.Router.extend({

    routes: {
        'about': 'about',
        'help': 'help',
        'contribute': 'contribute'
    },

    about: function()
    {

        console.log('Switching to about..');
    },

    help: function()
    {
        console.log('Switching to help..');
    },

    contribute: function()
    {
        console.log('Switching to contribute..');
    }
});

$(
    function()
    {
        new Router();

        Backbone.history.start({pushState: true});
    }
);
