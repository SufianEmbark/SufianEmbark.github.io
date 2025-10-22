using System.Collections;
using System.Collections.Generic;
using UnityEngine;

public class PlayerController : MonoBehaviour
{

    private float planeSpeed = 10f;                                       // Player Stats
    private float rotateSpeed = 100.0f;                                     //
    public float health;                                                   //

    private float horizontalInput;                                          //Movement Stats
    private Vector3 startPos = new Vector3(0.0f, 26.0f, -11.5f);            //
    public Vector3 pos;                                                     //
                                            // Movement Fixes
    private float sidesLimit = 37.5f;                                         //
    private float forwardLimit = 26.5f;                                       //
    private float backwardLimit = -20.8f;                                     //


    void Start()
    {
        health = 5;
        transform.position = startPos;
    }

    void Update()
    {
        //Getting Variables
        pos = transform.position;

        //Player Movement

        transform.Translate(Vector3.forward * planeSpeed * Time.deltaTime);            //Allways Move Forward
        horizontalInput = Input.GetAxis("Horizontal");                                 //Get Horizontal Value
        transform.Rotate(Vector3.up, horizontalInput * rotateSpeed * Time.deltaTime);  //Rotate Attached to Horizontal Value

        //Teleport Player To A New Opposite Position When Off Screen -----------------------------------------------------------------------------------------------------------------------------------------------------

        if (transform.position.z > forwardLimit) //If Player Gets Off Top Screen Appear in Opposite Position
        {
            transform.position = new Vector3(transform.position.x, transform.position.y, backwardLimit + 0.5f);  //Last +0.5 or -0.5 Fix the Teleporting Loop When Off Screen
        }

        if (transform.position.x > sidesLimit) //If Player Gets Off Right Screen Appear in Opposite Position
        {
            transform.position = new Vector3((transform.position.x * -1) + 0.5f, transform.position.y, transform.position.z);
        }

        if (transform.position.x < -sidesLimit) //If Player Gets Off Left Screen Appear in Opposite Position
        {
            transform.position = new Vector3((transform.position.x * -1) - 0.5f, transform.position.y, transform.position.z);
        }

        if (transform.position.z < backwardLimit) //If Player Gets Off Bottom Appear in Opposite Position
        {
            transform.position = new Vector3(transform.position.x, transform.position.y, forwardLimit - 0.5f);
        }
    }

    //Player Health Managing ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    private void Hit()
    {
        if (health < 2)          //If Last Life Destroy
        {
            Destroy(gameObject);
        }
        else                     //Else Quit a Life
        {
            health--;
        }
    }



    //Player Collisions ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    private void OnTriggerEnter(Collider other)
    {
        if (other.gameObject.CompareTag("Enemy Plane") || other.gameObject.CompareTag("Enemy Seagull")) //If Other Collider is a Plane or a Seagull Hit the Player and Destroy the Other Collider
        {
            Hit();
            Destroy(other.gameObject);

        }
    }

}
