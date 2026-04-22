package com.example.firebaseapp

import androidx.compose.runtime.*
import androidx.compose.foundation.layout.*
import androidx.compose.material.*
import androidx.compose.material3.Button
import androidx.compose.material3.Text
import androidx.compose.material3.TextField
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.google.firebase.firestore.FirebaseFirestore

@Composable
fun ChildProfileScreen() {
    var name by remember { mutableStateOf("") }
    var birthday by remember { mutableStateOf("") }
    var address by remember { mutableStateOf("") }

    Column(modifier = Modifier.padding(16.dp)) {
        TextField(value = name, onValueChange = { name = it }, label = { Text("Child's Name") })
        TextField(value = birthday, onValueChange = { birthday = it }, label = { Text("Birthday") })
        TextField(value = address, onValueChange = { address = it }, label = { Text("Address/Purok") })

        Spacer(modifier = Modifier.height(16.dp))

        Button(onClick = {
            val db = FirebaseFirestore.getInstance()
            val childData = hashMapOf("name" to name, "birthday" to birthday, "address" to address)
            db.collection("children").add(childData)
        }) {
            Text("Save Profile")
        }
    }
}
