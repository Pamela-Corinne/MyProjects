package com.example.firebaseapp

import android.content.Intent
import android.os.Bundle
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.auth.FirebaseAuth
import com.google.firebase.firestore.ktx.firestore
import com.google.firebase.ktx.Firebase

class LoginActivity : AppCompatActivity() {

    private lateinit var auth: FirebaseAuth
    private val db = Firebase.firestore

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_login_screen)

        auth = FirebaseAuth.getInstance()

        val emailEditText = findViewById<EditText>(R.id.emailEditText)
        val passwordEditText = findViewById<EditText>(R.id.passwordEditText)
        val loginButton = findViewById<Button>(R.id.loginButton)
        val signUpTextView = findViewById<TextView>(R.id.signUpTextView)

        loginButton.setOnClickListener {
            loginButton.isEnabled = false  // Disable the button
            val email = emailEditText.text.toString()
            val password = passwordEditText.text.toString()

            // Check if email is the hardcoded admin
            if (email == "admin@panel.com" && password == "admin123") {
                startActivity(Intent(this, AdminActivity::class.java))
                finish()
            } else {
                // Authenticate with Firebase
                auth.signInWithEmailAndPassword(email, password)
                    .addOnCompleteListener(this) { task ->
                        loginButton.isEnabled = true  // Re-enable the button
                        if (task.isSuccessful) {
                            val userEmail = task.result?.user?.email ?: ""
                            // Check if user is approved using email
                            checkIfUserIsApproved(userEmail) { isApproved ->
                                if (isApproved) {
                                    Toast.makeText(baseContext, "Login Successful", Toast.LENGTH_SHORT).show()
                                    startActivity(Intent(this, MainActivity::class.java))
                                    finish()
                                } else {
                                    Toast.makeText(baseContext, "Account Pending Approval", Toast.LENGTH_SHORT).show()
                                    auth.signOut() // Sign out the user if not approved
                                }
                            }
                        } else {
                            Toast.makeText(baseContext, "Login Failed", Toast.LENGTH_SHORT).show()
                        }
                    }
            }
        }

        signUpTextView.setOnClickListener {
            startActivity(Intent(this, SignUpActivity::class.java))
            finish()
        }
    }

    // Updated: Check approval using email
    private fun checkIfUserIsApproved(email: String, callback: (Boolean) -> Unit) {
        db.collection("approvedUsers")
            .whereEqualTo("email", email)
            .get()
            .addOnSuccessListener { documents ->
                callback(!documents.isEmpty)
            }
            .addOnFailureListener {
                callback(false) // Assume not approved if there's an error
            }
    }
}
