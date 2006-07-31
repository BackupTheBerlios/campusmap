
/** @author Gunnar
 *  
 *  @description Implements the output of Files conected to the object selected in the viewport. 
 * 
 **/
package CampusMap;

import java.awt.Color;

import javax.swing.event.HyperlinkEvent;
import java.util.EventListener;
import java.awt.event.MouseListener;
import java.awt.event.MouseEvent;
import javax.swing.event.HyperlinkListener;
import javax.swing.text.html.*;
import javax.swing.text.*;
import javax.swing.*;

import java.io.*;
import java.net.*;
import java.util.regex.*;

/**
 * 
 */
public class Explications extends JPanel implements Runnable{
	private String actualText;
	private URL folderPrefix;
	public JEditorPane textDisplay;
	private Thread getTextThread;
	private int threadCounter=0;
	private BufferedReader in; 
	private String buffer;
	private boolean lookForCoordsThisRun=false;
	Matcher matchString;
	private Pattern coordFinder = Pattern.compile("([a-z]{0,2})Coords (\\w{1,3}-[\\d{0,2}|k|K].\\w{1,3} )*([^a-z ]*) *([^a-z ]*) (Tour)*");
	protected Environment env; 
	private EventListener browserEventListener;
	
	
	public Explications(Environment p_env)
	{
		env = p_env;
		env.objectInitDisplay.setText("Explications");
		actualText = new String();
		try{
			folderPrefix = new URL(Environment.phpRoot);
			textDisplay = new JEditorPane("text/html", "");
			textDisplay.setBackground(Environment.bg_Color);
			browserEventListener = new InnerHyperListener(this);
			

			// Behavior for form elements
			textDisplay.setEditorKit (new HTMLEditorKit() {
				  public ViewFactory getViewFactory() {
				    return (HTMLFactory)browserEventListener;
				  }
				});

			// HTML Editorkit definition MUST happen before the text-set!
			
			textDisplay.setEditable(false);
			textDisplay.setPage(folderPrefix);
			
			// Behavior for normal Hyperlinks
			textDisplay.addHyperlinkListener((HyperlinkListener)browserEventListener);
			
						
		}catch(Exception e){ 
			System.out.println("Text-Field initialissation failed.");
			e.printStackTrace();
			textDisplay.setText("Keine Verbindung zum HTML Verzeichnis. Bitte überprüfen sie ihre Internetverbindung, oder lassen sie den Webmaster der FH-Lübeck von dem Fehler wissen. Danke!");
		}
		textDisplay.setIgnoreRepaint(true);
		this.add(textDisplay);
		this.setBackground(Color.WHITE);
		//this.setPreferredSize(new java.awt.Dimension(570, 300));
		
	}
	
	/********************************************************
	 * Inner class 
	 * Behavior for Hyperlinks
	 * 
	 */ 
	class InnerHyperListener extends HTMLEditorKit.HTMLFactory implements HyperlinkListener{
		private Explications expl;
		
		public InnerHyperListener(Explications p_expl){
			expl = p_expl;
		}

		public View create(Element elem) {
	        Object o = elem.getAttributes().getAttribute(
	                          StyleConstants.NameAttribute);
	        if (o instanceof HTML.Tag) {
	          HTML.Tag kind = (HTML.Tag) o;
	          if (kind == HTML.Tag.INPUT)
	            return new FormView(elem) {
	              protected void submitData (String data) {
	      	    	env.setToolTip("loading", 0);
	      	    	try{
	      	    		getTextForInput(new URL(folderPrefix+"index.php?"+data), true);
	      	    	}catch(MalformedURLException e){
	      	    		e.printStackTrace();
	      	    	}
	              }
//		              protected  void imageSubmit(String data) {
//		      	    	env.setToolTip("loading", 0);
//		                System.out.println ("Data: "+data);
//		              }  
	            };
	        }
	        return super.create (elem);
	      }
		
	    public void hyperlinkUpdate(HyperlinkEvent e) {
            if (e.getEventType() == HyperlinkEvent.EventType.ACTIVATED) {
        		
    	    	Environment.setToolTip("loading", 0);
	            JEditorPane pane = (JEditorPane) e.getSource();
	            getTextForInput(e.getURL(), true);
	        }
	    }
	} 
	/**
	 * Inner class
	 */

	
	public void getTextForInput(URL input, boolean lookForCoords)
	{
		System.out.println(input);
		actualText = new String();
		lookForCoordsThisRun = lookForCoords;
		
		env.showLoadingLayer("please wait while loading new text-content");
		try{
	        textDisplay.setContentType("text/html");
	        
	        in = new BufferedReader(new InputStreamReader(input.openStream()));
			buffer = new String();
			getTextThread=new Thread(this);
			getTextThread.setPriority(Thread.MIN_PRIORITY);
			getTextThread.setName("getTextThread"+(threadCounter++));
			getTextThread.start();
	        
		}catch (IOException e) {
			e.printStackTrace();
		}
	}
	
	
	public void run(){
		boolean found=false;
		try{
			while((buffer = in.readLine())!=null){
				actualText+=buffer;
				env.increaseLoadingBar();
				
				if(lookForCoordsThisRun){
					matchString = coordFinder.matcher(buffer);
					if(!found && buffer!=""){
						found = matchString.find();
						if(found){
							System.out.println("gefunden:"+matchString.group(0));
						   	if(!((String)matchString.group(1)).equals("no") &&
						   				 matchString.groupCount()>=4){
						   		getCoordsFromUrl(buffer);
					    	} else {
					    		env.theContent.setTouring(false, null);
					    		Environment.clearToolTip();
					    	}
				        }
					}
				}
			}
			in.close();
			env.showLoadingLayer("please wait while display renders");
			Document doc = textDisplay.getDocument();
		    doc.remove(0, doc.getLength());
		    Reader r = new StringReader(actualText);
		    EditorKit kit = textDisplay.getEditorKit();
	            kit.read(r, doc, 0);
			
		}catch(IOException ioe){
			ioe.printStackTrace();
		} catch(BadLocationException ble){
			ble.printStackTrace();
		}
		env.hideLoadingLayer();
	}
	
	public void getCoordsFromUrl(String locator){
		//System.out.println(buffer);
		try{
			/************************************
			 *  roomnumber parsing
			 */
			String roomNumber = matchString.group(2);
			int firstSeperator = roomNumber.indexOf('-');
			int secondSeperator = roomNumber.indexOf('.');
	
			String buildingNumber = roomNumber.substring(0,firstSeperator);
			String levelNumberString = roomNumber.substring(firstSeperator+1, secondSeperator);
			float levelNumber = 0;
			if (levelNumberString.equalsIgnoreCase("K")) //Keller
				levelNumber = -0.6f;
			else
				levelNumber = Integer.parseInt(levelNumberString);
			int thisRoomHeight = 0;//
			System.out.println("buildingNumber:"+buildingNumber+", levelNumber:  "+levelNumber+", thisRoomHeight: "+thisRoomHeight);
			/************************************
			 *  Coordinates from the database. Here, life on stage!
			 */
			float x = Float.parseFloat(matchString.group(3).replace(',','.'))/100;
			float y = Float.parseFloat(matchString.group(4).replace(',','.'))/100;
	//    		System.out.println(matchString.group(5)+" "+matchString.group(3)+" "+matchString.group(4));
	
			/************************************
			 * Is this a REAL tour? (multiple buildungs in a row)
			 */
	    	if(matchString.group(5)!=null) {
	    		Environment.setToolTip("Tour läuft. Bitte einen Knopf unten drücken um weiter zu navigieren.", 9999);
	    	}else Environment.setToolTip("Raumansicht. Bitte Knopf unten drücken um weiter zu navigieren.", 9999);
			/************************************
			 * invoking the move
			 */
			FVector roomPos = new FVector(x,y,thisRoomHeight);
			Building tempBuild = env.theContent.objectManager.getBuildingByNumber(buildingNumber);
			if(tempBuild!=null){
//				System.out.println("building: "+tempBuild);
				env.theContent.setTouring(true, tempBuild);
				//env.theContent.spheres.add(tempBuild.myPos.multiply(env.theContent.getBuildingUniformScale()));
				tempBuild.myPos.multiply(env.theContent.getBuildingUniformScale()).printMe("myPos");
				
				// Calculate the virtual floor height
				FVector convRoomPos = tempBuild.myPos.multiply(env.theContent.getBuildingUniformScale()).add(tempBuild.convertDatabasePos(roomPos));
				//convRoomPos.printMe("convRoomPos");
				//env.theContent.spheres.add(convRoomPos);
				convRoomPos.setZ(levelNumber*12 + 6);
				
				env.theContent.theCamera.guaranteeMinInteractiveCameraHeight();
				env.theContent.objectManager.resetBuildings();
				env.theContent.prepareForDetailDraw(convRoomPos, true);
			}
		}catch(NumberFormatException e){
			e.printStackTrace();
			env.theContent.setTouring(false, null);
			Environment.setToolTip("Fehler bei der Datenbehandlung. Entschuldigen Sie bitte und benachrichtigen Sie den Administrator.", 3);
		}

	}

} // end Explications



