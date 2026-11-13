const {Schema, model} = require("mongoose");
const { type } = require("node:os");

const userSchema = new Schema ({
    name: {type: String, required: true},
    surname: {type: String, required: true},
    username: {type: String, required: true},
    email: {type: String, required: true},
    passwordHash: {type: String, required: true},
});

userSchema.set('toJson', {
    transform: (document, returnedObject) => {
        returnedObject.id = returnedObject._id
        delete returnedObject._id
        
        delete returnedObject.passwordHash
    }
})

const User  = model('User', userSchema);
module.exports = User;