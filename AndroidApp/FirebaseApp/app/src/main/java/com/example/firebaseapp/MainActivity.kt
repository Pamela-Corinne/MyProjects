package com.example.firebaseapp

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.auth.FirebaseAuth

class MainActivity : AppCompatActivity() {

    private lateinit var auth: FirebaseAuth

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        auth = FirebaseAuth.getInstance()

        val addChildProfileButton = findViewById<Button>(R.id.addChildProfileButton)

        // Check if user is already logged in
        if (auth.currentUser != null) {
            // Do nothing here, just keep MainActivity
        } else {
            //If not logged in, show login/signup buttons
            val loginButton = findViewById<Button>(R.id.loginButton)
            val signUpButton = findViewById<Button>(R.id.signUpButton)

            loginButton.setOnClickListener {
                startActivity(Intent(this, LoginActivity::class.java))
            }

            signUpButton.setOnClickListener {
                startActivity(Intent(this, SignUpActivity::class.java))
            }
        }

        addChildProfileButton.setOnClickListener {
            //If logged in, navigate to AddChildProfileActivity
            startActivity(Intent(this, AddChildProfileActivity::class.java))
        }
    }
}